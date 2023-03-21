<?php
/**
 * @author      Laurent Jouanneau
 * @contributor
 * @copyright   2021 3liz
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
require_once(__DIR__.'/SamlAbstractInstaller.php');

class samlModuleUpgrader_loginpass extends SamlAbstractInstaller {

    public $targetVersions = array('2.0.0-alpha.1');
    public $date = '2021-09-23 10:12';

    function install()
    {
        $currentAfterLogin = '';
        $currentAfterLogout = '';

        // remove after_login and after_logout from [saml:sp]
        // we are now using after_login and after_logout from the auth configuration
        $ini = $this->entryPoint->getSingleMainConfigIni();
        if ($ini->getValue('after_login', 'saml:sp')) {
            $currentAfterLogin = $ini->getValue('after_login', 'saml:sp');
        }

        if ($ini->getValue('after_logout', 'saml:sp')) {
            $currentAfterLogout = $ini->getValue('after_logout', 'saml:sp');
        }

        if (!method_exists('jApp', 'varConfigPath')) {
            // the modifier of mainconfig is in read only mode into Jelix 1.8,
            // so we don't call it.
            $ini->removeValue('after_login', 'saml:sp');
            $ini->removeValue('after_logout', 'saml:sp');
        }

        $ini = $this->entryPoint->getSingleLocalConfigIni();
        if ($ini->getValue('after_login', 'saml:sp')) {
            $currentAfterLogin = $ini->getValue('after_login', 'saml:sp');
        }

        if ($ini->getValue('after_logout', 'saml:sp')) {
            $currentAfterLogout = $ini->getValue('after_logout', 'saml:sp');
        }

        if (!method_exists('jApp', 'varConfigPath')) {
            // bug into jelix 1.8.0-alpha.4, so we don't call it.
            $ini->removeValue('after_login', 'saml:sp');
            $ini->removeValue('after_logout', 'saml:sp');
        }

        if (strpos($currentAfterLogin, 'saml~') === 0) {
            $currentAfterLogin = '';
        }

        if (strpos($currentAfterLogout, 'saml~') === 0) {
            $currentAfterLogout = '';
        }

        /**
         * @var jIniFileModifier $originalAuthConfigIni
         * @var array $driverConfig
         */
        list($authConfigIni,
            $driverConfig,
            $authConfigFileName,
            $driverName) = $this->getAuthConfAndDriver();

        if (!$authConfigIni || $driverName != 'saml') {
            throw new Exception("no saml plugin activated?");
        }

        $appConfig = $this->entryPoint->getConfigObj();
        $hasJcommunity = isset($appConfig->modules['jcommunity.access']) &&
                         $appConfig->modules['jcommunity.access'] > 0;

        $logoutAction = ($hasJcommunity?'jcommunity~login:out':'jauth~login:out');
        $formAction = ($hasJcommunity?'jcommunity~login:index':'jauth~login:form');

        // setup configuration parameters that were missing from the original
        // saml.coord.ini.php

        if ($authConfigIni->getValue('on_error_action') == "saml~auth:notauthenticated") {
            $authConfigIni->setValue('on_error_action', $logoutAction);
        }

        if ($authConfigIni->getValue('on_ajax_error_action') === null) {
            $authConfigIni->setValue('on_ajax_error_action', '');
        }

        if ($authConfigIni->getValue('bad_ip_action') == '') {
            $authConfigIni->setValue('bad_ip_action', $logoutAction);
        }

        if ($authConfigIni->getValue('on_error_sleep') === null) {
            $authConfigIni->setValue('on_error_sleep', 0);
        }

        if ($authConfigIni->getValue('after_login') == '') {
            $authConfigIni->setValue('after_login',
                                     $currentAfterLogin ?:
                                     $appConfig->startModule.'~'.$appConfig->startAction);
        }

        if ($authConfigIni->getValue('after_logout') == '') {
            $authConfigIni->setValue('after_logout', $currentAfterLogout ?: $formAction);
        }

        if ($authConfigIni->getValue('enable_after_login_override') === null) {
            $authConfigIni->setValue('enable_after_login_override', 'on');
        }

        if ($authConfigIni->getValue('enable_after_logout_override') === null) {
            $authConfigIni->setValue('enable_after_logout_override', 'on');
        }

        if ($authConfigIni->getValue('url_return_external_allowed_domains') == null) {
            $authConfigIni->setValue('url_return_external_allowed_domains', '');
        }

        if ($authConfigIni->getValue('persistant_cookie_name') == '') {
            $authConfigIni->setValue('persistant_cookie_name', 'jauthSession');
        }

        $authConfigIni->save();

        $this->updateCacheProfile('profiles.ini.php');
        $this->updateCacheProfile('profiles.ini.php.dist');
    }
}
