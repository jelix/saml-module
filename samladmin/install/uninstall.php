<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2023 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class samladminModuleUninstaller extends \Jelix\Installer\Module\Uninstaller
{
    public function uninstall(Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        if (class_exists('jAcl2DbManager')) {
            \jAcl2DbManager::deleteRight('saml.config.access');
        }
    }
}
