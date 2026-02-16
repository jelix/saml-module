<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2019-2026 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use \Jelix\Installer\Module\API\InstallHelpers;

require_once(__DIR__.'/SamlInstallerTrait.php');

/**
 *
 */
class samlModuleInstaller extends \Jelix\Installer\Module\Installer
{
    use SamlInstallerTrait;

    public function install(InstallHelpers $helpers)
    {
        jFile::createDir(jApp::varConfigPath('saml/certs'));

        $this->updateCacheProfile('profiles.ini.php');

        $dbProfile = $helpers->getConfigIni()->getValue('dbProfile', 'saml');
        $mapper = new jDaoDbMapper($dbProfile);
        $mapper->createTableFromDao('saml~saml_account');
    }
}
