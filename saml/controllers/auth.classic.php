<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use OneLogin\Saml2\Auth;

/**
 * Controller to call from the app to login and logout close to the Identity Provider
 */
class authCtrl extends jController {

    public $pluginParams = array(
        '*' => array('auth.required'=>false)
    );

    /**
     * initiate a SAML Authorization request
     *
     * When the user visits this URL, the browser will be redirected to the SSO
     * IdP with an authorization request. If successful, it will then be
     * redirected to the method acs() of the endpoint controller, with the auth
     * details.
     *
     * @return jResponseRedirectUrl
     * @throws \OneLogin\Saml2\Error
     */
    function login() {
        /** @var jResponseRedirectUrl $rep */
        $rep = $this->getResponse('redirectUrl');

        $router = jApp::coord();
        if (!$router->originalAction->isEqualTo($router->action) &&
            $_SERVER['REQUEST_METHOD'] == 'GET'
        ) {
            // internal redirection from the coordinator plugin, and the current
            // request is a GET request
            $relayState = jUrl::getFull($router->originalAction->toString(), $this->request->params);
        }
        else {
            // the user has clicked on a link that point directly to the login() method
            // or the internal redirection is made during a non GET request.
            // we will then redirect the user to the default page to display
            // after a login.
            $afterLoginAction = (jApp::config()->{'saml:sp'})['after_login'];
            if ($afterLoginAction) {
                // page indicated into the after_login option
                $relayState = jUrl::getFull($afterLoginAction);
            } else {
                // home page
                $relayState = $this->request->getServerURI() . jApp::urlBasePath();
            }
        }

        $configuration = new \Jelix\Saml\Configuration($this->request);
        $auth = new Auth($configuration->getSettingsArray());

        $rep->url = $auth->login($relayState, array(), false, false, true);
        $rep->addHttpHeader('Pragma', 'no-cache');
        $rep->addHttpHeader('Cache-Control', 'no-cache, must-revalidate');
        return $rep;
    }

    /**
     * initiate a SAML Single Log Out request.
     *
     * When the user visits this URL, the browser will be redirected to the SLO
     * IdP with an SLO request.
     *
     * @return jResponseRedirectUrl
     * @throws \OneLogin\Saml2\Error
     */
    function logout() {
        $rep = $this->getResponse('redirectUrl');

        $configuration = new \Jelix\Saml\Configuration($this->request);
        $auth = new Auth($configuration->getSettingsArray());

        $sessionIndex = null;
        $nameId = null;
        $nameIdFormat = null;
        $nameIdNameQualifier = null;
        $nameIdSPNameQualifier = null;

        $hasSAMLSession = false;
        if (isset($_SESSION['IdPSessionIndex']) && !empty($_SESSION['IdPSessionIndex'])) {
            $sessionIndex = $_SESSION['IdPSessionIndex'];
            $hasSAMLSession = true;
        }
        if (isset($_SESSION['samlNameId'])) {
            $nameId = $_SESSION['samlNameId'];
            $hasSAMLSession = true;
        }
        if (isset($_SESSION['samlNameIdFormat'])) {
            $nameIdFormat = $_SESSION['samlNameIdFormat'];
        }
        if (isset($_SESSION['samlNameIdNameQualifier'])) {
            $nameIdNameQualifier = $_SESSION['samlNameIdNameQualifier'];
        }
        if (isset($_SESSION['samlNameIdSPNameQualifier'])) {
            $nameIdSPNameQualifier = $_SESSION['samlNameIdSPNameQualifier'];
        }

        jAuth::logout();

        if (!$hasSAMLSession) {
            // to avoid error "unknown session" on the IdP side
            $rep = $this->getResponse('redirect');
            $afterLogoutAction = (jApp::config()->{'saml:sp'})['after_logout'];
            if ($afterLogoutAction) {
                $rep->action = $afterLogoutAction;
            } else {
                $rep->action = 'saml~endpoint:logoutdone';
            }
            return $rep;
        }

        unset($_SESSION['samlUserdata']);
        unset($_SESSION['IdPSessionIndex']);
        unset($_SESSION['samlNameId']);
        unset($_SESSION['samlNameIdFormat']);
        unset($_SESSION['samlNameIdNameQualifier']);
        unset($_SESSION['samlNameIdSPNameQualifier']);

        $afterLogoutAction = (jApp::config()->{'saml:sp'})['after_logout'];
        if ($afterLogoutAction) {
            // page indicated into the after_login option
            $relayState = jUrl::getFull($afterLogoutAction);
        } else {
            // home page
            $relayState = jUrl::getFull('saml~endpoint:logoutdone');
        }
        $rep->url = $auth->logout($relayState, array(), $nameId,
            $sessionIndex, true, $nameIdFormat, $nameIdNameQualifier, $nameIdSPNameQualifier);

        $rep->addHttpHeader('Pragma', 'no-cache');
        $rep->addHttpHeader('Cache-Control', 'no-cache, must-revalidate');

        return $rep;
    }

    function notauthenticated() {
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        if ($rep->bodyTpl == '') {
            $rep->bodyTpl = 'saml~main_error';
        }
        $rep->title = jLocale::get('saml~auth.authentication.error.not.authenticated.title');
        $tpl = new jTpl();

        if ($this->param('error')) {
            $rep->setHttpStatus('401', 'Unauthorized');
            $tpl->assign('error', $this->param('error'));
        }
        else {
            $tpl->assign('error', '');
        }
        $rep->body->assign('MAIN', $tpl->fetch('notauthenticated'));
        return $rep;
    }

    function authenticated() {
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        if ($rep->bodyTpl == '') {
            $rep->bodyTpl = 'saml~main_error';
        }
        $rep->title = jLocale::get('saml~auth.authenticated.title');
        $tpl = new jTpl();
        $rep->body->assign('MAIN', $tpl->fetch('authenticated'));
        return $rep;
    }
}

