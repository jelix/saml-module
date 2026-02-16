<?php

/**
 * @author     Laurent Jouanneau
 * @copyright  2026 Laurent Jouanneau
 *
 * @link     https://jelix.org
 * @licence MIT
 */

class SamlFormZone extends jZone
{
    protected $_tplname = 'saml~loginform';

    protected function _prepareTpl()
    {

        $url = '';
        $forceLoginForm = false;
        if (jApp::coord()->request) {
            $url = jApp::coord()->request->getParam('auth_url_return');
            $forceLoginForm =  jApp::coord()->request->getParam('loginform');
        }
        $idpConfig = jApp::config()->{'saml:idp'};
        if (isset($idpConfig['label']) && $idpConfig['label']) {
            $label = $idpConfig['label'];
        }
        else {
            $label = 'SAML';
        }

        $authLabel = jLocale::get('saml~auth.authentication.login.button', array($label));

        $samlConfig = jApp::config()->{'saml'};
        $showOnlySaml = isset($samlConfig['forceSAMLAuthOnLoginPage']) ? $samlConfig['forceSAMLAuthOnLoginPage'] && !$forceLoginForm : false;
        $redirectToSaml = isset($samlConfig['forceRedirectToSAMLAuthOnLoginPage']) ? $samlConfig['forceRedirectToSAMLAuthOnLoginPage'] && $showOnlySaml : false;

        $this->_tpl->assign('button_label', $authLabel);
        $this->_tpl->assign('auth_url_return', $url);
        $this->_tpl->assign('showOnlySaml',  $showOnlySaml);
        $this->_tpl->assign('redirectToSaml',  $redirectToSaml);
    }
}
