<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019-2022 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 * Trait for configurator for Jelix 1.7+
 */
trait SamlInstallerTrait {


    protected function updateCacheProfile($fileName = 'profiles.ini.php')
    {

        $filePath = jApp::varConfigPath($fileName);

        if (!file_exists($filePath)) {
            return;
        }

        $profiles = new \Jelix\IniFile\IniModifier($filePath);

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
