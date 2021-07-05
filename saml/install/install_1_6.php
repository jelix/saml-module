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

    protected function getAuthConfAndDriver() {
        $ini = $this->getAuthConf();
        if ($ini === null) {
            return array(null, null);
        }
        $confIni = parse_ini_file($ini->getFileName(), true);

        require_once(JELIX_LIB_PATH.'auth/jAuth.class.php');
        $authConfig = jAuth::loadConfig($confIni);
        $driverConfig = $authConfig[$authConfig['driver']];
        if ($authConfig['driver'] == 'Db' ||
            (isset($driverConfig['compatiblewithdb']) &&
                $driverConfig['compatiblewithdb'])
        ) {
            return array($ini, $authConfig[$authConfig['driver']]);
        }
        return array(null, null);
    }

    protected function getAuthConf() {
        $authconfig = $this->entryPoint->localConfigIni->getValue('auth','coordplugins');
        if (!$authconfig) {
            return null;
        }
        $confPath = jApp::configPath($authconfig);
        $conf = new jIniFileModifier($confPath);
        return $conf;
    }


    function install() {
        if ($this->entryPoint->type == 'cmdline')
            return;


        // retrieve current DAO if possible, if there are already installed other auth modules
        list($authConfigIni, $driverConfig) = $this->getAuthConfAndDriver();


        // create the saml.coord.ini.php if needed
        jFile::createDir(jApp::configPath('saml/certs'));
        $authConfigfile = jApp::configPath('saml/saml.coord.ini.php');
        $authConfigfileIsNew = false;
        if ($this->firstExec('saml:installconfigfile')) {
            if (!file_exists($authConfigfile)) {
                $this->copyFile('saml.coord.ini.php', $authConfigfile);
                $authConfigfileIsNew = true;
            }
        }

        // fill saml.coord.ini.php with setting indicating the current dao/form/profile
        $authconfig =  new jIniFileModifier($authConfigfile);
        if ($authConfigfileIsNew && $driverConfig) {
            $authconfig->setValue('dao', $driverConfig['dao'], 'saml');
            $authconfig->setValue('profile', $driverConfig['profile'], 'saml');
            $authconfig->setValue('form', $driverConfig['form'], 'saml');
            $authconfig->setValue('uploadsDirectory', $driverConfig['uploadsDirectory'], 'saml');
        }


        // declare saml.coord.ini.php
        $samlconfig = $this->config->getValue('saml','coordplugins');
        $samlconfigMaster = $this->config->getValue('saml','coordplugins', null, true);
        if (!$samlconfig && !$samlconfigMaster) {
            $entrypointconfig = $this->config->getOverrider();
            $entrypointconfig->setValue('saml', 'saml/saml.coord.ini.php', 'coordplugins');
            $entrypointconfig->setValue('saml.name', 'auth', 'coordplugins');
            $entrypointconfig->removeValue('auth', 'coordplugins');

        }

        // import SAML configuation into localconfig or mainconfig
        $iniConfig = $this->getAppConfig();
        if (!$iniConfig->isSection('saml:sp')) {
            $samlIniConfig = new jIniFileModifier(__DIR__.'/config.ini');
            $iniConfig->import($samlIniConfig);
            $iniConfig->save();
        }

        $driver = $iniConfig->getValue('driver', 'coordplugin_auth');
        if ($driver && $driver != 'saml') {
            $iniConfig->setValue('driver', 'saml', 'coordplugin_auth');
        }

        $responseClass = $iniConfig->getValue('htmlauth', 'responses');
        if (!$responseClass) {
            $iniConfig->setValue('htmlauth', 'jResponseHtml', 'responses');
        }

        // create a first user if indicated
        $login = $this->getParameter('useradmin');
        $email =  $this->getParameter('emailadmin');
        if ($login && $this->firstDbExec()) {
            $daoSelector = $authconfig->getValue('dao', 'saml');
            $dbProfile = $authconfig->getValue('profile', 'saml');

            // be sure the table is created
            $mapper = new jDaoDbMapper($dbProfile);
            $mapper->createTableFromDao($daoSelector);

            $dao = jDao::get($daoSelector, $dbProfile);
            $user = $dao->getByLogin($login);
            if (!$user) {
                $user = jDao::createRecord($daoSelector, $dbProfile);
                $user->login = $login;
                $user->password = '!!saml';
                $user->email = ($email === null? '': $email);
                $dao->insert($user);
            }
        }

        $authconfig->save();

        // if jAcl2 is activated, remove some rights.
        if ($this->firstExec('saml:acl2')) {
            if (class_exists('jAcl2DbManager')) {
                $groups = jDao::get('jacl2db~jacl2group', 'jacl2_profile')->findAll();
                foreach($groups as $group) {
                    $id = $group->id_aclgrp;
                    jAcl2DbManager::removeRight($id, 'auth.user.change.password', '-', true);
                    jAcl2DbManager::removeRight($id, 'auth.users.change.password', '-', true);
                    //jAcl2DbManager::removeRight($id, 'auth.users.create', '-', true);
                    //jAcl2DbManager::removeRight($id, 'auth.users.delete', '-', true);
                }

                if ($login) {
                    jAcl2DbUserGroup::createUser($login, false);
                    if (jAcl2DbUserGroup::getGroup('admins')) {
                        jAcl2DbUserGroup::addUserToGroup($login, 'admins');
                    }
                }
            }
        }
    }
}
