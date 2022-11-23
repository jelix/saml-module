<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019-2021 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * base class for installers/updaters for Jelix 1.6
 */
class SamlAbstractInstaller extends jInstallerModule {

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
    protected function getAuthConfAndDriver($pluginName = 'auth')
    {
        list($ini, $confFileName) = $this->getAuthConf($pluginName);
        if ($ini === null) {
            return array(null, null, null, null);
        }
        $confIni = parse_ini_file($ini->getFileName(), true);

        require_once(JELIX_LIB_PATH.'auth/jAuth.class.php');
        $authConfig = jAuth::loadConfig($confIni);

        $appConfig = $this->getAppConfig();

        $driver = $appConfig->getValue('driver', 'coordplugin_auth');
        if (!$driver) {
            $driver = $authConfig['driver'];
        }

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

    /**
     * @return [jIniFileModifier, string]
     * @throws Exception
     */
    protected function getAuthConf($pluginName = 'auth')
    {
        $authconfig = $this->entryPoint->localConfigIni->getValue($pluginName,'coordplugins');
        if (!$authconfig) {
            return array(null, null);
        }
        $confPath = jApp::configPath($authconfig);
        $conf = new jIniFileModifier($confPath);
        return array($conf, $authconfig);
    }


    protected function updateCacheProfile($fileName = 'profiles.ini.php')
    {

        if (method_exists('jApp', 'varConfigPath')) {
            $filePath = jApp::varConfigPath($fileName);
        }
        else {
            $filePath = jApp::configPath($fileName);
        }

        if (!file_exists($filePath)) {
            return;
        }


        $profiles = new jIniFileModifier($filePath);

        if ($profiles->getValue('saml', 'jcache') || $profiles->isSection('jcache:saml')) {
            return;
        }

        $default = $profiles->getValue('default', 'jcache');
        if ($default) {
            $profiles->setValue('saml', $default, 'jcache');
        }
        else if ($profiles->isSection('jcache:default')) {
            $profiles->setValue('saml', 'default', 'jcache');
        }
        else {
            $profiles->setValues(array(
                 'enabled' => true,
                 'driver' => 'file',
                 'ttl' => 60 * 60 * 24 ,
                 'automatic_cleaning_factor' => 2,
                 //'cache_dir' => '',
                 'file_locking' => 1,
                 'directory_level' => 3,
                 'file_name_prefix' => 'saml_',
            ), 'jcache:saml');
        }
        $profiles->save();
    }

}
