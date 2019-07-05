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
        if ($router->originalAction->isEqualTo($router->action)) {
            // the user has clicked on a link that point directly to the login() method
            $relayState = $this->request->getServerURI().jApp::urlBasePath();
        }
        else {
            // internal redirection from the coordinator plugin
            $relayState = jUrl::getFull($router->originalAction->toString(), $this->request->params);
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

        if (isset($_SESSION['IdPSessionIndex']) && !empty($_SESSION['IdPSessionIndex'])) {
            $sessionIndex = $_SESSION['IdPSessionIndex'];
        }
        if (isset($_SESSION['samlNameId'])) {
            $nameId = $_SESSION['samlNameId'];
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

        $relayState = jUrl::getFull('saml~auth:notauthenticated');

        $rep->url = $auth->logout($relayState, array(), $nameId,
            $sessionIndex, true, $nameIdFormat, $nameIdNameQualifier, $nameIdSPNameQualifier);

        // jAuth::logout() will be made on the endpoint:slo action
        return $rep;
    }

    function notauthenticated() {
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        $rep->setHttpStatus('401', 'Unauthorized');
        $rep->title = 'Not authenticated';
        $tpl = new jTpl();
        $tpl->assign('error', '');
        $rep->body->assign('MAIN', $tpl->fetch('notauthenticated'));
        return $rep;
    }
}

