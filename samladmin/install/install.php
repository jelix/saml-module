<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2022 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 */
class samladminModuleInstaller extends \Jelix\Installer\Module\Installer
{
    public function install(\Jelix\Installer\Module\API\InstallHelpers $helpers)
    {
        // if jAcl2 is activated, remove some rights.
        if (class_exists('jAcl2DbManager')) {
            jAcl2DbManager::addSubject('saml.config.access', 'samladmin~admin.config.title');
            if (jAcl2DbUserGroup::getGroup('admins')) {
                jAcl2DbManager::addRight('admins', 'saml.config.access');
            }
        }
    }
}
