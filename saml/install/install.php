<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019-2022 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use \Jelix\Installer\Module\API\InstallHelpers;

require_once(__DIR__.'/SamlInstallerTrait.php');

/**
 * Installer for Jelix 1.7+
 */
class samlModuleInstaller extends \Jelix\Installer\Module\Installer
{
    use SamlInstallerTrait;

    public function install(InstallHelpers $helpers)
    {

        jFile::createDir(jApp::varConfigPath('saml/certs'));

        $this->updateCacheProfile('profiles.ini.php');
        $firstEp = true;
        foreach ($helpers->getEntryPointsByType() as $ep) {
            /**
             * @var jIniFileModifier $originalAuthConfigIni
             * @var array $driverConfig
             */
            list(
                $originalAuthConfigIni,
                $driverConfig,
                $authConfigFileName,
                $driverName
                ) = $this->getAuthConfAndDriver($ep, 'auth');

            if ($driverName != 'saml' || !$driverConfig) {
                continue;
            }

            $dbProfile = $originalAuthConfigIni->getValue('profile', 'saml');
            $mapper = new jDaoDbMapper($dbProfile);
            $mapper->createTableFromDao('saml~saml_account');

            // create a first user if indicated
            $login = $this->getParameter('useradmin');
            $email = $this->getParameter('emailadmin');
            if ($login && $firstEp) {
                $daoSelector = $originalAuthConfigIni->getValue('dao', 'saml');
                $dbProfile = $originalAuthConfigIni->getValue('profile', 'saml');

                // be sure the table is created
                $mapper->createTableFromDao($daoSelector);

                $dao = jDao::get($daoSelector, $dbProfile);
                $user = $dao->getByLogin($login);
                if (!$user) {
                    $user = jDao::createRecord($daoSelector, $dbProfile);
                    $user->login = $login;
                    $user->password = password_hash('AdminSaml', 1);
                    $user->email = ($email === null ? '' : $email);
                    $dao->insert($user);
                }
                // if jAcl2 is activated, remove some rights.
                if (class_exists('jAcl2DbUserGroup')) {
                    jAcl2DbUserGroup::createUser($login, false);
                    if (jAcl2DbUserGroup::getGroup('admins')) {
                        jAcl2DbUserGroup::addUserToGroup($login, 'admins');
                    }
                }
            }
            $firstEp = false;
        }
    }

    /**
     *
     * @return [jIniFileModifier, , string, ]
     *      - the ini file of the plugin
     *      - the configuration of the authentication driver (if plugin is auth)
     *      - configuration file name
     *      - driver name
     * @throws jException
     */
    protected function getAuthConfAndDriver(\Jelix\Installer\EntryPoint $ep, $pluginName = 'auth')
    {
        $pluginConfInfo = $ep->getCoordPluginConfig($pluginName);
        if ($pluginConfInfo === null || $pluginConfInfo[1] != 0) {
            return array(null, null, null, null);
        }
        $ini = $pluginConfInfo[0];
        $confFileName = $ep->getConfigIni()->getValue($pluginName, 'coordplugins');

        $confIni = parse_ini_file($ini->getFileName(), true);

        require_once(JELIX_LIB_PATH.'auth/jAuth.class.php');
        $authConfig = jAuth::loadConfig($confIni);
        $driver = $authConfig['driver'];

        if (!isset($authConfig[$driver])) {
            return array($ini, null, $confFileName, $driver);
        }

        $driverConfig = $authConfig[$driver];
        if ($driver == 'Db' ||
            (isset($driverConfig['compatiblewithdb']) &&
                $driverConfig['compatiblewithdb'])
        ) {
            return array($ini, $driverConfig, $confFileName, $driver);
        }
        return array(null, null, null, null);
    }
}
