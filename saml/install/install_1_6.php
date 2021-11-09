<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

require_once(__DIR__.'/SamlAbstractInstaller.php');
/**
 */
class samlModuleInstaller extends SamlAbstractInstaller
{

    function install() {
        if ($this->entryPoint->type == 'cmdline') {
            return;
        }

        jFile::createDir(jApp::configPath('saml/certs'));

        /**
         * @var jIniFileModifier $originalAuthConfigIni
         * @var array $driverConfig
         */
        list(
            $originalAuthConfigIni,
            $driverConfig,
            $authConfigFileName,
            $driverName
            ) = $this->getAuthConfAndDriver();

        $setupSamlSection = false;

        if (!$originalAuthConfigIni) {
            // there is no configuration file

            $authConfigFileName = 'saml/saml.coord.ini.php';
            $authConfigFilePath = jApp::configPath($authConfigFileName);
            if (!file_exists($authConfigFilePath)) {
                $this->copyFile('saml.coord.ini.php', $authConfigFilePath);
            }
            $originalAuthConfigIni = new jIniFileModifier($authConfigFilePath);
            $driverConfig = null;
        }

        if (!$originalAuthConfigIni->isSection('saml')) {
            // there is no saml section in the configuration file
            if ($driverConfig) {
                // retrieve current DAO if possible, if there are already installed other auth modules
                // fill saml.coord.ini.php with setting indicating the current dao/form/profile
                $originalAuthConfigIni->setValue('dao', $driverConfig['dao'], 'saml');
                $originalAuthConfigIni->setValue('profile', $driverConfig['profile'], 'saml');
                $originalAuthConfigIni->setValue('form', $driverConfig['form'], 'saml');
                if (isset($driverConfig['userform'])) {
                    $originalAuthConfigIni->setValue('userform', $driverConfig['userform'], 'saml');
                }
                $originalAuthConfigIni->setValue('uploadsDirectory', $driverConfig['uploadsDirectory'], 'saml');
            }
            else {
                $originalAuthConfigIni->setValue('dao', "jauthdb~jelixuser", 'saml');
                $originalAuthConfigIni->setValue('profile', "", 'saml');
                $originalAuthConfigIni->setValue('form', "jauthdb_admin~jelixuser", 'saml');
                $originalAuthConfigIni->setValue('uploadsDirectory', '', 'saml');
            }
            $originalAuthConfigIni->setValue('compatiblewithdb', true, 'saml');
            $originalAuthConfigIni->setValue('automaticAccountCreation', true, 'saml');
            $originalAuthConfigIni->setValue('driver', 'saml');
            $setupSamlSection = true;
        }

        // declare the coord plugin saml
        $appConfig = $this->getAppConfig();
        $epConfig = $this->entryPoint->getEpConfigIni();

        $samlconfig = $epConfig->getValue('auth','coordplugins');
        $samlconfigMaster = $appConfig->getValue('auth','coordplugins');
        if (!$samlconfig && !$samlconfigMaster) {
            $epConfig->setValue('auth', $authConfigFileName, 'coordplugins');
        }

        $epConfig->setValue('auth.class', 'samlCoordPlugin', 'coordplugins');

        // import SAML configuration into localconfig or mainconfig

        if (!$appConfig->isSection('saml:sp')) {
            $samlIniConfig = new jIniFileModifier(__DIR__.'/config.ini');

            if (strpos($appConfig->getFileName(), 'mainconfig.ini.php') === false) {
                // import configuration from mainconfig into localconfig
                // it can have parameters that are empty by default, and then
                // values from mainconfig should not be set with empty values into localconfig
                $mainConfig = $this->entryPoint->getSingleMainConfigIni();
                if ($mainConfig->isSection('saml:sp')) {
                    $samlIniConfig->setValues($mainConfig->getValues('saml:sp'), 'saml:sp');
                }
                if ($mainConfig->isSection('saml:idp')) {
                    $samlIniConfig->setValues($mainConfig->getValues('saml:idp'), 'saml:idp');
                }
                if ($mainConfig->isSection('saml:security')) {
                    $samlIniConfig->setValues($mainConfig->getValues('saml:security'), 'saml:security');
                }
            }

            $appConfig->import($samlIniConfig);
            $appConfig->save();
        }

        $driver = $appConfig->getValue('driver', 'coordplugin_auth');
        if (!$setupSamlSection && $driver != 'saml') {
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
            $daoSelector = $originalAuthConfigIni->getValue('dao', 'saml');
            $dbProfile = $originalAuthConfigIni->getValue('profile', 'saml');

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

        $originalAuthConfigIni->save();

        // if jAcl2 is activated, remove some rights.
        if ($this->firstExec('saml:acl2')) {
            if (class_exists('jAcl2DbUserGroup') && $login) {
                jAcl2DbUserGroup::createUser($login, false);
                if (jAcl2DbUserGroup::getGroup('admins')) {
                    jAcl2DbUserGroup::addUserToGroup($login, 'admins');
                }
            }
        }

        $this->updateCacheProfile('profiles.ini.php');
        $this->updateCacheProfile('profiles.ini.php.dist');
    }
}
