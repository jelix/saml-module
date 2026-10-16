<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2026 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once(__DIR__.'/SamlAbstractInstaller.php');

class samlModuleUpgrader_samlloginconf extends SamlAbstractInstaller {

    public $targetVersions = array('2.10.0-beta.1');
    public $date = '2026-10-16 18:00';

    function install()
    {
        $this->migrateLoginParam($this->entryPoint->getSingleMainConfigIni());
        $this->migrateLoginParam($this->entryPoint->getSingleLocalConfigIni());
        $this->migrateLoginParam($this->entryPoint->liveConfigIni);
    }

    protected function migrateLoginParam($ini)
    {
        $login = $ini->getValue('__login', 'saml:attributes-mapping');
        if ($login !== null) {
            $ini->setValue('login', $login, 'saml:attributes-mapping');
            $ini->removeValue('__login', 'saml:attributes-mapping');
        }
    }
}
