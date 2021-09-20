<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 */
class samlModuleInstaller extends jInstallerModule {

    /**
     * @return jIniFileModifier
     */
    protected function getAppConfig()
    {
        if ($this->getParameter('localconfig')) {
            $appConfig = $this->entryPoint->localConfigIni->getMaster();
        } else {
            $appConfig = $this->entryPoint->configIni->getMaster();
        }
        if ($appConfig instanceof jIniMultiFilesModifier) {
            $appConfig = $appConfig->getOverrider();
        }
        return $appConfig;
    }

    /**
     * @return [jIniFileModifier, array]
     * @throws jException
     */
    protected function getAuthConfAndDriver()
    {
        list($ini, $confFileName) = $this->getAuthConf();
        if ($ini === null) {
            return array(null, null, null);
        }
        $confIni = parse_ini_file($ini->getFileName(), true);

        require_once(JELIX_LIB_PATH.'auth/jAuth.class.php');
        $authConfig = jAuth::loadConfig($confIni);
        $driverConfig = $authConfig[$authConfig['driver']];
        if ($authConfig['driver'] == 'Db' ||
            (isset($driverConfig['compatiblewithdb']) &&
                $driverConfig['compatiblewithdb'])
        ) {
            return array($ini, $authConfig[$authConfig['driver']], $confFileName);
        }
        return array(null, null, null);
    }

    /**
     * @return [jIniFileModifier, string]
     * @throws Exception
     */
    protected function getAuthConf() {
        $authconfig = $this->entryPoint->localConfigIni->getValue('auth','coordplugins');
        if (!$authconfig) {
            return array(null, null);
        }
        $confPath = jApp::configPath($authconfig);
        $conf = new jIniFileModifier($confPath);
        return array($conf, $authconfig);
    }


    function install() {
        if ($this->entryPoint->type == 'cmdline') {
            return;
        }

        jFile::createDir(jApp::configPath('saml/certs'));

        /**
         * @var jIniFileModifier $originalAuthConfigIni
         * @var array $driverConfig
         */
        list($originalAuthConfigIni, $driverConfig, $authConfigFileName) = $this->getAuthConfAndDriver();

        if (!$originalAuthConfigIni || !$originalAuthConfigIni->isSection('saml')) {
            // there is no saml section in the configuration file
            // let's install our own saml.coord.ini.php file.

            $authConfigFileName = 'saml/saml.coord.ini.php';

            $authConfigfileIsNew = false;
            $authConfigfile = jApp::configPath($authConfigFileName);
            if (!file_exists($authConfigfile)) {
                $this->copyFile('saml.coord.ini.php', $authConfigfile);
                $authConfigfileIsNew = true;
            }

            // retrieve current DAO if possible, if there are already installed other auth modules
            // fill saml.coord.ini.php with setting indicating the current dao/form/profile
            $authConfig =  new jIniFileModifier($authConfigfile);
            if ($authConfigfileIsNew && $driverConfig) {
                $authConfig->setValue('dao', $driverConfig['dao'], 'saml');
                $authConfig->setValue('profile', $driverConfig['profile'], 'saml');
                $authConfig->setValue('form', $driverConfig['form'], 'saml');
                if (isset($driverConfig['userform'])) {
                    $authConfig->setValue('userform', $driverConfig['userform'], 'saml');
                }
                $authConfig->setValue('uploadsDirectory', $driverConfig['uploadsDirectory'], 'saml');
            }
        }
        else {
            // the current auth coord file has already a saml section
            // we don't touch it
            $authConfigfile = jApp::configPath($authConfigFileName);
            $authConfig =  new jIniFileModifier($authConfigfile);
        }

        // declare the coord plugin saml
        $epConfig = $this->entryPoint->getEpConfigIni();
        $samlconfig = $epConfig->getValue('saml','coordplugins');
        $samlconfigMaster = $epConfig->getValue('saml','coordplugins', null, true);
        if (!$samlconfig && !$samlconfigMaster) {
            $epConfig->setValue('saml', $authConfigFileName, 'coordplugins');
            $epConfig->setValue('saml.name', 'auth', 'coordplugins');
            $epConfig->setValue('auth', '', 'coordplugins');
        }

        // import SAML configuration into localconfig or mainconfig
        $appConfig = $this->getAppConfig();
        if (!$appConfig->isSection('saml:sp')) {
            $samlIniConfig = new jIniFileModifier(__DIR__.'/config.ini');
            $appConfig->import($samlIniConfig);
            $appConfig->save();
        }

        $driver = $appConfig->getValue('driver', 'coordplugin_auth');
        if ($driver && $driver != 'saml') {
            $appConfig->setValue('driver', 'saml', 'coordplugin_auth');
        }

        $responseClass = $this->entryPoint->localConfigIni->getValue('htmlauth', 'responses');
        if (!$responseClass) {
            $appConfig->setValue('htmlauth', 'jResponseHtml', 'responses');
        }

        // create a first user if indicated
        $login = $this->getParameter('useradmin');
        $email =  $this->getParameter('emailadmin');
        if ($login && $this->firstDbExec()) {
            $daoSelector = $authConfig->getValue('dao', 'saml');
            $dbProfile = $authConfig->getValue('profile', 'saml');

            // be sure the table is created
            $mapper = new jDaoDbMapper($dbProfile);
            $mapper->createTableFromDao($daoSelector);

            $dao = jDao::get($daoSelector, $dbProfile);
            $user = $dao->getByLogin($login);
            if (!$user) {
                $user = jDao::createRecord($daoSelector, $dbProfile);
                $user->login = $login;
                $user->password = password_hash('AdminSaml', 1);
                $user->email = ($email === null? '': $email);
                $dao->insert($user);
            }
        }

        $authConfig->save();

        // if jAcl2 is activated, remove some rights.
        if ($this->firstExec('saml:acl2')) {
            if (class_exists('jAcl2DbUserGroup') && $login) {
                jAcl2DbUserGroup::createUser($login, false);
                if (jAcl2DbUserGroup::getGroup('admins')) {
                    jAcl2DbUserGroup::addUserToGroup($login, 'admins');
                }
            }
        }
    }
}
