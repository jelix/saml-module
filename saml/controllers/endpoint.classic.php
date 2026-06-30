<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2026 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Saml\Saml;
use OneLogin\Saml2\Error;

/**
 * Controller called by the Identity Provider
 */
class endpointCtrl extends jController {

    public $pluginParams = array(
        '*' => array('auth.required'=>false)
    );

    function metadata() {


        if ($this->param('download')) {
            /**
             * @var jResponseBinary $xml
             */
            $xml = $this->getResponse('binary');
            $xml->doDownload = true;
            $xml->outputFileName = 'saml-metadata.xml';
        }
        else {
            /**
             * @var jResponseXml $xml
             */
            $xml = $this->getResponse('xml');
            $xml->sendXMLHeader = false;
            $xml->checkValidity = false;
        }

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


        return $xml;
    }

    /**
     * SP Assertion Consumer Service Endpoint
     *
     * Called when the user has been authenticated at the identity provider
     */
    function acs() {

        try {
            $configuration = new \Jelix\Saml\Configuration();

            $saml = new Saml($configuration);
        }
        catch (\Exception $e)
        {
            \jLog::logEx($e, 'error');
            Saml::logError($e->getMessage(), 'ACS config');
        }

        try {
            $authUser = $saml->processLoginResponse($this->request);
        }
        catch(\Jelix\Saml\ProcessException $e) {
            Saml::logError($e->getMessage(), 'ACS');
            jAuthentication::authenticationFail();
            return $this->acsError(implode(', ', $e->getSamlErrors()));
        }
        catch(\Jelix\Saml\LoginException $e) {
            jAuthentication::authenticationFail();
            if ($e->getCode() == $saml::ACS_ERR_NOT_AUTHENTICATED) {
                return $this->acsError();
            }
            return $this->acsError($e->getMessage());
        }
        catch(Error $e) {
            Saml::logError($e->getMessage(), 'ACS');
            return $this->acsError('Technical error');
        }
        catch(Exception $e) {
            \jLog::logEx($e, 'error');
            Saml::logError($e->getMessage(), 'ACS');
            return $this->acsError('Technical error');
        }

        $samlSession = $authUser->getAttribute('samlSession');
        $urlBack = $samlSession['relayState'];
        $params = array(
            'login' => $authUser->getLogin(),
            'failed' => 1,
            //'urlback' => $urlBack
        );
        $failUrl = jUrl::get('authcore~sign:in', $params);

        /** @var samlauthIdentityProvider $idp  */
        $idp = jAuthentication::manager()->getIdpById('samlauth');
        $workflow = jAuthentication::startAuthenticationWorkflow($authUser, $idp);
        $workflow->setFinalUrl($urlBack);
        $workflow->setFailUrl($failUrl);
        $nextUrl = $workflow->getNextAuthenticationUrl();
        if (!$workflow->isSuccess()) {
            $_SESSION['LOGINPASS_ERROR'] = $workflow->getErrorMessage();
        }
        else {
            unset($_SESSION['LOGINPASS_ERROR']);
        }

        return $this->redirectToUrl($nextUrl);
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
    function sls()
    {
        try {
            $configuration = new \Jelix\Saml\Configuration();

            $saml = new Saml($configuration);
        }
        catch (\Exception $e)
        {
            \jLog::logEx($e, 'error');
            Saml::logError($e->getMessage(), 'ACS config');
        }

        try {
            $relayState = $saml->processLogout($this->request);
            jAuthentication::session()->unsetSessionUser();
        }
        catch(\Jelix\Saml\ProcessException $e) {
            Saml::logError($e->getMessage(), 'SLS');
            return $this->logoutresult(implode(', ', $e->getSamlErrors()));
        }
        catch(Error $e) {
            Saml::logError($e->getMessage(), 'SLS');
            return $this->logoutresult('Technical error');
        }
        catch(\Exception $e) {
            \jLog::logEx($e, 'error');
            Saml::logError($e->getMessage(), 'SLS');
            return $this->logoutresult('Technical error');
        }

        if ($relayState) {
            return $this->redirectToUrl($relayState);
        }

        return $this->logoutresult();
    }

    protected function acsError($error = '')
    {
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
