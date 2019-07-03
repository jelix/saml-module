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
        return $appConfig;
    }

    function install() {
        if ($this->entryPoint->type == 'cmdline')
            return;

        jFile::createDir(jApp::configPath('saml/certs'));

        $samlconfig = $this->config->getValue('saml','coordplugins');
        $samlconfigMaster = $this->config->getValue('saml','coordplugins', null, true);
        $authConfigfile = jApp::configPath('saml/saml.coord.ini.php');
        if (!$samlconfig && !$samlconfigMaster) {
            $entrypointconfig = $this->config->getOverrider();
            $entrypointconfig->setValue('saml','saml/saml.coord.ini.php','coordplugins');
            $entrypointconfig->setValue('saml.name','auth','coordplugins');
            $entrypointconfig->removeValue('auth','coordplugins');


            if ($this->firstExec('saml:installconfigfile')) {
                if (!file_exists($authConfigfile)) {
                    $this->copyFile('saml.coord.ini.php', $authConfigfile);
                }
            }
        }

        $iniConfig = $this->getAppConfig();
        if (!$iniConfig->isSection('saml:sp')) {
            $samlIniConfig = new jIniFileModifier(__DIR__.'/config.ini');
            $iniConfig->import($samlIniConfig);
            $iniConfig->save();
        }

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
            }
        }

        $authconfig =  new jIniFileModifier($authConfigfile);
        $daoName = $authconfig->getValue('dao', 'saml');
        if ($daoName == 'jauthdb~jelixuser' && $this->firstDbExec()) {
            $this->execSQLScript('install_jauth.schema', 'jauthdb');
            $login = $this->getParameter('useradmin');
            $email =  $this->getParameter('emailadmin');
            if ($login) {
                $cn = $this->dbConnection();
                $rs = $cn->query("SELECT usr_login FROM ".$cn->prefixTable('jlx_user')." WHERE usr_login = ".$cn->quote($login));
                if (!$rs->fetch()) {
                    /*require_once(JELIX_LIB_PATH.'auth/jAuth.class.php');
                    require_once(__DIR__.'/../plugins/auth/saml/saml.auth.php');

                    $confIni = parse_ini_file(jApp::configPath($authconfig), true);
                    $authConfig = jAuth::loadConfig($confIni);
                    $driver = new samlAuthDriver($authConfig['saml']);
                    $passwordHash = $driver->cryptPassword('admin');*/
                    $passwordHash = '!!saml';
                    $cn->exec("INSERT INTO ".$cn->prefixTable('jlx_user')." (usr_login, usr_password, usr_email ) VALUES
                                (".$cn->quote($login).", ".$cn->quote($passwordHash)." , ".$cn->quote($email).")");
                }

                if (class_exists('jAcl2DbUserGroup')) {
                    jAcl2DbUserGroup::createUser($login);
                    jAcl2DbUserGroup::addUserToGroup($login, 'admins');
                }
            }
        }
    }
}