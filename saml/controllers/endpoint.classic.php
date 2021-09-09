<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2021 3Liz
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

        $configuration = new \Jelix\Saml\Configuration($this->request);

        $samlSettings = $configuration->getSettings();

        $sp = $samlSettings->getSPData();

        $sd = $configuration->getSettings()->getSecurityData();
        $authnsign = $sd['authnRequestsSigned'];
        $wsign = $sd['wantAssertionsSigned'];

        $validUntil = null;
        $cacheDuration = null;
        $contacts = $configuration->getSettings()->getContacts();
        $organization = $configuration->getSettings()->getOrganization();
        $attributes = array();

        $samlMetadata = Metadata::builder($sp, $authnsign, $wsign, $validUntil, $cacheDuration, $contacts, $organization, $attributes);
        $xml->content = $samlMetadata;

        /*
        $samlMetadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);
        if (!empty($errors)) {
            throw new Error(
                'Invalid SP metadata: '.implode(', ', $errors),
                Error::METADATA_SP_INVALID
            );
        }
         */

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

        $configuration = new \Jelix\Saml\Configuration($this->request);

        $samlSettings = $configuration->getSettingsArray();
        $auth = new Auth($samlSettings);
        $auth->processResponse();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            jLog::log(implode(', ', $errors)."\n".$auth->getLastErrorReason(),'error');
            return $this->acsError(implode(', ', $errors));
        }

        if (!$auth->isAuthenticated()) {
            return $this->acsError();
        }

        $loginAttr = $configuration->getLoginAttribute();
        $attributes = $auth->getAttributes();
        if (empty($attributes)) {
            return $this->acsError(jLocale::get('saml~auth.authentication.error.saml.attributes.missing', array($loginAttr)));
        }

        if (!isset($attributes[$loginAttr])) {
            return $this->acsError(jLocale::get('saml~auth.authentication.error.saml.attribute.missing', array($loginAttr)));
        }
        $login = $attributes[$loginAttr];
        if (is_array($login)) {
            $login = $login[0];
        }

        // indicate the attributes to the driver
        /** @var samlAuthDriver $samlDriver */
        $samlDriver = jAuth::getDriver();
        $samlDriver->setAttributesMapping($attributes, $configuration->getAttributesMapping());

        // now we can login. A user will be probably created, with the saml attributes
        // given to the driver
        if (!jAuth::login($login, '!!saml')) {
            return $this->acsError(jLocale::get('saml~auth.authentication.error.not.authorized'));
        }

        $_SESSION['samlUserdata'] = $auth->getAttributes();
        $_SESSION['IdPSessionIndex'] = $auth->getSessionIndex();
        $_SESSION['samlNameId'] = $auth->getNameId();
        $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
        $_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
        $_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();

        if (isset($_POST['RelayState']) && $_POST['RelayState'] != jUrl::getFull('saml~endpoint:acs')) {
            $relayState = $_POST['RelayState'];
        }
        else {
            $afterLoginAction = (jApp::config()->{'saml:sp'})['after_login'];
            if ($afterLoginAction) {
                // page indicated into the after_login option
                $relayState = jUrl::getFull($afterLoginAction);
            } else {
                // home page
                $relayState = $this->request->getServerURI() . jApp::urlBasePath();
            }
        }


        /** @var jResponseRedirectUrl $rep */
        $rep = $this->getResponse('redirectUrl');
        $rep->url = $relayState;
        return $rep;
    }

    /**
     * SP Single Logout Service Endpoint
     *
     * @return jResponseHtml
     * @throws Error
     */
    function sls() {
        $configuration = new \Jelix\Saml\Configuration($this->request);
        $auth = new Auth($configuration->getSettingsArray());


        $url = $auth->processSLO(true, null, true, null, true);
        $errors = $auth->getErrors();

        if (empty($errors)) {
            jAuth::logout();
            unset($_SESSION);
            /*
            unset($_SESSION['samlUserdata']);
            unset($_SESSION['IdPSessionIndex']);
            unset($_SESSION['samlNameId']);
            unset($_SESSION['samlNameIdFormat']);
            unset($_SESSION['samlNameIdNameQualifier']);
            unset($_SESSION['samlNameIdSPNameQualifier']);
            */

            if ($url) {
                /** @var jResponseRedirectUrl $rep */
                $rep = $this->getResponse('redirectUrl');
                $rep->url = $url;
                return $rep;
            }
            $relayState = $this->param('RelayState');
            if ($relayState) {
                /** @var jResponseRedirectUrl $rep */
                $rep = $this->getResponse('redirectUrl');
                $rep->url = $relayState;
            }
            else {
                $afterLogoutAction = (jApp::config()->{'saml:sp'})['after_logout'];
                if ($afterLogoutAction) {
                    // page indicated into the after_logout option
                    $url = jUrl::getFull($afterLogoutAction);
                    /** @var jResponseRedirectUrl $rep */
                    $rep = $this->getResponse('redirectUrl');
                    $rep->url = $url;
                }
                else {
                    $rep = $this->logoutresult();
                }
            }
        } else {
            $rep = $this->logoutresult(implode(', ', $errors)."\n".$auth->getLastErrorReason());
        }
        return $rep;
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

