<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2026 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Metadata;

/**
 * Controller called by the Identity Provider
 */
class endpointCtrl extends jController {

    public $pluginParams = array(
        '*' => array('auth.required'=>false)
    );

    function metadata() {
        $xml = $this->getResponse('xml');

        try {
            $configuration = new \Jelix\Saml\Configuration(false);
            // we don't check saml attributes and idp settings, no need to generate metadata.
            $configuration->checkSpConfig();
            $samlSettings = $configuration->getSettings(true);

            $xml->content = $samlSettings->getSPMetadata();
        }
        catch(\Exception $e) {

            $response = $this->getResponse('basichtml');
            $response->htmlFile = JELIX_LIB_CORE_PATH.'response/error.en_US.php';
            $response->addContent('<p>'.htmlspecialchars($e->getMessage()).'</p>');

            $response->setHttpStatus('500', 'Internal server error');
            return $response;
        }

        $xml->sendXMLHeader = false;
        $xml->checkValidity = false;
        return $xml;
    }

    /**
     * SP Assertion Consumer Service Endpoint
     *
     * Called when the user has been authenticated at the identity provider
     */
    function acs() {
        $configuration = new \Jelix\Saml\Configuration();

        $saml = new Jelix\Saml\Saml($configuration);

        try {
            $relayState = $saml->processLoginResponse($this->request);
        }
        catch(\Jelix\Saml\ProcessException $e) {
            jLog::log($e->getMessage(),'error');
            return $this->acsError(implode(', ', $e->getSamlErrors()));
        }
        catch(\Jelix\Saml\LoginException $e) {
            if ($e->getCode() == $saml::ACS_ERR_NOT_AUTHENTICATED) {
                return $this->acsError();
            }
            return $this->acsError($e->getMessage());
        }

        /** @var jResponseRedirectUrl $rep */
        $rep = $this->getResponse('redirectUrl');
        $rep->url = $relayState;
        return $rep;
    }

    /**
     * SP Single Logout Service Endpoint
     *
     * Called by the IdP, when the user has logout from the application
     * (the controller receives an SAMLResponse and display a page), or
     * when the user has logout from the IdP (the controller receives an
     * SAMLRequest and returns an SAMLResponse).
     *
     * @return jResponseHtml|jResponseRedirectUrl
     *
     * @throws Error
     */
    function sls() {
        $configuration = new \Jelix\Saml\Configuration();

        $saml = new Jelix\Saml\Saml($configuration);

        try {
            $relayState = $saml->processLogout($this->request);
        }
        catch(\Jelix\Saml\ProcessException $e) {
            jLog::log($e->getMessage(),'error');
            return $this->logoutresult(implode(', ', $e->getSamlErrors()));
        }
        catch(\Exception $e) {
            return $this->logoutresult($e->getMessage());
        }

        if ($relayState) {
            /** @var jResponseRedirectUrl $rep */
            $rep = $this->getResponse('redirectUrl');
            $rep->url = $relayState;
            return $rep;
        }

        return $this->logoutresult();
    }

    protected function acsError($error = '') {
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        if ($rep->bodyTpl == '') {
            $rep->bodyTpl = 'saml~main_error';
        }

        if ($error) {
            $rep->setHttpStatus('503', 'Service Unavailable');
        }
        else {
            $rep->setHttpStatus('401', 'Unauthorized');
        }
        $rep->title = jLocale::get('saml~auth.authentication.error.title');
        $tpl = new jTpl();
        $tpl->assign('error', $error);
        $rep->body->assign('MAIN', $tpl->fetch('notauthenticated'));
        return $rep;
    }

    public function logoutdone() {
        return $this->logoutresult();
    }


    protected function logoutresult($error = '') {
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        if ($rep->bodyTpl == '') {
            $rep->bodyTpl = 'saml~main_error';
        }

        $rep->title = jLocale::get('saml~auth.logout.title');
        $tpl = new jTpl();

        if ($error) {
            $rep->setHttpStatus('503', 'Service Unavailable');
            $tpl->assign('error', $error);
        }
        else {
            $tpl->assign('error', '');
        }
        $rep->body->assign('MAIN', $tpl->fetch('logout'));
        return $rep;
    }

}

