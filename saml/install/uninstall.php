<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2023 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class samlModuleUninstaller extends \Jelix\Installer\Module\Uninstaller
{
    public function uninstall(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        $this->removeCacheProfile();

        $appConfig = $helpers->getConfigIni();
        $appConfig->removeValue('auth.class', 'coordplugins');
        $appConfig->removeValue('driver', 'coordplugin_auth');
        $appConfig->removeSection('saml:sp');
        $appConfig->removeSection('saml:idp');
        $appConfig->removeSection('saml:security');
        $appConfig->removeSection('saml:sp:requestedAttributes');
        $appConfig->removeSection('saml:attributes-mapping');
        $appConfig->removeSection('saml:security');
        $appConfig->removeSection('saml');
        $appConfig->save();

        foreach ($helpers->getEntryPointsByType() as $ep) {
            $epConfig = $ep->getConfigIni();
            $epConfig->removeValue('auth.class', 'coordplugins');
            $epConfig->save();
        }

        $helpers->removeDirectoryContent('varconfig:saml');
    }


    protected function removeCacheProfile($fileName = 'profiles.ini.php')
    {

        $filePath = jApp::varConfigPath($fileName);

        if (!file_exists($filePath)) {
            return;
        }

        $profiles = new \Jelix\IniFile\IniModifier($filePath);
        $profiles->removeSection('jcache:saml');
        $profiles->removeValue('saml', 'jcache');
        $profiles->save();
    }
}
