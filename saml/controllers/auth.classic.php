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
     * @return jResponseRedirectUrl|jResponseHtml
     * @throws \OneLogin\Saml2\Error
     */
    function login() {
        /** @var jResponseRedirectUrl $rep */
        $rep = $this->getResponse('redirectUrl');
        $configuration = new \Jelix\Saml\Configuration();

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
            // FIXME JelixAuthentication should provides a method to retrieve the
            // url of the default page, url that it should be
            $relayState = jServer::getServerURI().jApp::urlBasePath();

        }

        try {

            $saml = new Jelix\Saml\Saml(
                $configuration,
                jApp::coord()->getPlugin('auth')->config
            );

            $rep->url = $saml->startLoginProcess($relayState);

        }
        catch(\Exception $e) {
            /** @var jResponseHtml $rep */
            jLog::log($e->getMessage(), 'error');
            $rep = $this->getResponse('htmlauth');
            if ($rep->bodyTpl == '') {
                $rep->bodyTpl = 'saml~main_error';
            }
            $rep->title = jLocale::get('saml~auth.authentication.error.title');
            $tpl = new jTpl();
            $rep->body->assign('MAIN', $tpl->fetch('configerror'));
            return $rep;

        }
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
        /** @var jResponseRedirectUrl $rep */
        $rep = $this->getResponse('redirectUrl');

        $configuration = new \Jelix\Saml\Configuration();
        $saml = new Jelix\Saml\Saml(
            $configuration,
            jApp::coord()->getPlugin('auth')->config
        );
        $defaultRelayState = jUrl::getFull('saml~endpoint:logoutdone');
        $rep->url = $saml->startLogoutProcess($defaultRelayState);

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

