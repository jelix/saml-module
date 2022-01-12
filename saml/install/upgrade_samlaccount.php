<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2022 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once(__DIR__.'/SamlAbstractInstaller.php');

class samlModuleUpgrader_samlaccount extends SamlAbstractInstaller {

    public $targetVersions = array('2.0.0-rc.2');
    public $date = '2022-01-05 18:00';

    function install()
    {
        $ini = $this->entryPoint->getSingleLocalConfigIni();
        $ini->setValue('allowSAMLAccountToUseLocalPassword', true, 'saml');

        if ($this->firstDb()) {
            /**
             * @var jIniFileModifier $originalAuthConfigIni
             * @var array $driverConfig
             */
            list($originalAuthConfigIni, ) = $this->getAuthConfAndDriver();
            if ($originalAuthConfigIni) {
                $dbProfile = $originalAuthConfigIni->getValue('profile', 'saml');
                $mapper = new jDaoDbMapper($dbProfile);
                $mapper->createTableFromDao('saml~saml_account');
            }
        }
    }
}
