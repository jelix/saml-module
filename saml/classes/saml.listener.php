<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2021 3liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

class samlListener extends jEventListener{

    /**
     * @param jEvent $event
     */
    function onJauthLoginFormExtraBefore ($event)
    {
        $tpl = new jTpl();
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

        $tpl->assign('button_label', $authLabel);
        $tpl->assign('auth_url_return', $url);
        $tpl->assign('showOnlySaml',  $showOnlySaml);
        $tpl->assign('redirectToSaml',  $redirectToSaml);
        $event->add($tpl->fetch('saml~loginform'));

    }

    function onJauthLoginFormExtraAfter($event)
    {
        $forceLoginForm = false;
        if (jApp::coord()->request) {
            $forceLoginForm =  jApp::coord()->request->getParam('loginform');
        }
        $samlConfig = jApp::config()->{'saml'};
        if (isset($samlConfig['forceSAMLAuthOnLoginPage']) && $samlConfig['forceSAMLAuthOnLoginPage'] && !$forceLoginForm) {
            $event->add('</div>');
        }
    }

}
