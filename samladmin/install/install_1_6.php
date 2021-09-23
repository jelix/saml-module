<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2021 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

/**
 */
class samladminModuleInstaller extends jInstallerModule
{

    function install()
    {
        // if jAcl2 is activated, remove some rights.
        if ($this->firstExec('samladmin:acl2')) {
            if (class_exists('jAcl2DbManager')) {
                jAcl2DbManager::addSubject('saml.config.access', 'samladmin~admin.config.title');
                if (jAcl2DbUserGroup::getGroup('admins')) {
                    jAcl2DbManager::addRight('admins', 'saml.config.access');
                }
            }
        }
    }
}
