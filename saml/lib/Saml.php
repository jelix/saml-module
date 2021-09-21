<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2021 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;



use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;

class Saml
{

    const ACS_ERR_NOT_AUTHENTICATED = 1;
    const ACS_ERR_ATTR_MISSING = 2;
    const ACS_ERR_NOT_AUTHORIZED = 3;



    /**
     * @var Configuration
     */
    protected $config;

    /**
     * Configuration of the authentication saml plugin for the jelix coordinator
     * @var array
     */
    protected $authConfig;

    /**
     * @param Configuration  $configuration
     * @param array $authConf Configuration of the authentication saml plugin for the jelix coordinator
     */
    function __construct(Configuration $configuration, $authConf)
    {
        $this->config = $configuration;
        $this->authConfig = $authConf;
    }


    function startLoginProcess($returnUrl)
    {
        $auth = new Auth($this->config->getSettingsArray());

        return $auth->login($returnUrl, array(), false, false, true);
    }

    /**
     * @param  \jClassicRequest|\jRequest  $request
     *
     * @return string url to redirect to.
     * @throws Error
     * @throws \OneLogin\Saml2\ValidationError
     * @throws LoginException
     * @throws ProcessException
     */
    function processLoginResponse($request)
    {
        $samlSettings = $this->config->getSettingsArray();
        $auth = new Auth($samlSettings);
        $auth->processResponse();


        $errors = $auth->getErrors();
        if (!empty($errors)) {
            throw new ProcessException($auth);
        }

        if (!$auth->isAuthenticated()) {
            throw new LoginException('', self::ACS_ERR_NOT_AUTHENTICATED);
        }

        $loginAttr = $this->config->getLoginAttribute();
        $attributes = $auth->getAttributes();
        if (empty($attributes)) {
            throw new LoginException(
                \jLocale::get('saml~auth.authentication.error.saml.attributes.missing', array($loginAttr)),
                self::ACS_ERR_ATTR_MISSING
            );
        }

        if (!isset($attributes[$loginAttr])) {
            throw new LoginException(
                \jLocale::get('saml~auth.authentication.error.saml.attribute.missing', array($loginAttr)),
                self::ACS_ERR_ATTR_MISSING
            );
        }

        $login = $attributes[$loginAttr];
        if (is_array($login)) {
            $login = $login[0];
        }

        // indicate the attributes to the driver
        /** @var \samlAuthDriver $samlDriver */
        $samlDriver = \jAuth::getDriver();
        $samlDriver->setAttributesMapping($attributes, $this->config->getAttributesMapping());
        $password = $samlDriver->activateAuthWithSaml();

        // now we can login. A user will be probably created, with the saml attributes
        // given to the driver
        if (!\jAuth::login($login, $password)) {
            throw new LoginException(
                \jLocale::get('saml~auth.authentication.error.not.authorized'),
                self::ACS_ERR_NOT_AUTHORIZED
            );
        }

        $_SESSION['samlUserdata'] = $auth->getAttributes();
        $_SESSION['IdPSessionIndex'] = $auth->getSessionIndex();
        $_SESSION['samlNameId'] = $auth->getNameId();
        $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
        $_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
        $_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();

        $relayState = $request->getParam('RelayState');
        if (!$request->isPostMethod()
            || $relayState == ''
            || $relayState == \jUrl::getFull('saml~endpoint:acs')
        ) {

            if ($this->authConfig['after_login'] != '') {
                // page indicated into the after_login option
                $relayState = \jUrl::getFull($this->authConfig['after_login']);
            } else {
                // home page
                $relayState = $request->getServerURI() . \jApp::urlBasePath();
            }
        }
        return $relayState;
    }

    /**
     * @param string $defaultRelayState
     *
     * @return string the url to redirect to
     * @throws Error
     * @throws \jException
     */
    function startLogoutProcess($defaultRelayState)
    {
        $samlSettings = $this->config->getSettingsArray();
        $auth = new Auth($samlSettings);

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

        \jAuth::logout();

        if (!$hasSAMLSession) {
            // to avoid error "unknown session" on the IdP side
            if ($this->authConfig['after_logout']) {
                $url = \jUrl::getFull($this->authConfig['after_logout']);
            } else {
                $url = $defaultRelayState;
            }
            return $url;
        }

        unset($_SESSION['samlUserdata']);
        unset($_SESSION['IdPSessionIndex']);
        unset($_SESSION['samlNameId']);
        unset($_SESSION['samlNameIdFormat']);
        unset($_SESSION['samlNameIdNameQualifier']);
        unset($_SESSION['samlNameIdSPNameQualifier']);

        if ($this->authConfig['after_logout']) {
            // page indicated into the after_login option
            $relayState = \jUrl::getFull($this->authConfig['after_logout']);
        } else {
            // home page
            $relayState = $defaultRelayState;
        }
        $url = $auth->logout($relayState, array(), $nameId,
                             $sessionIndex, true, $nameIdFormat,
                             $nameIdNameQualifier, $nameIdSPNameQualifier);
        return $url;

    }



    /**
     *  @param  \jClassicRequest|\jRequest  $request
     *
     * @return
     * @throws Error
     */
    function processLogout($request)
    {
        $samlSettings = $this->config->getSettingsArray();
        $auth = new Auth($samlSettings);

        $url = $auth->processSLO(true, null, true, null, true);

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            throw new ProcessException($auth);
        }

        \jAuth::logout();
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
            return $url;
        }
        $relayState = $request->getParam('RelayState');
        if ($relayState) {
            return $relayState;
        }
        else {
            if ($this->authConfig['after_logout'] != '') {
                // page indicated into the after_logout option
                $url = \jUrl::getFull($this->authConfig['after_logout']);
                return $url;
            }
        }
        return '';
    }

}
