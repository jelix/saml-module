<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2021 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;



use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\LogoutRequest;

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

    protected function initCacheProfile()
    {
        try {
            \jProfiles::get('jcache', 'saml', true);
        }
        catch(\Exception $e) {
            try {
                $profile = \jProfiles::get('jcache', 'default', false);
                \jProfiles::createVirtualProfile('jcache', 'saml', $profile);
            }
            catch(\Exception $e) {
                \jProfiles::createVirtualProfile('jcache', 'saml', array(
                    'enabled' => true,
                    'driver' => 'file',
                    'ttl' => 60 * 60 * 24 ,
                    'automatic_cleaning_factor' => 2,
                    //'cache_dir' => '',
                    'file_locking' => 1,
                    'directory_level' => 3,
                    'file_name_prefix' => 'saml_',
                ));
            }
        }
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

        $loginAttr = $this->config->getSAMLAttributeForLogin();
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

        if ($this->config->isUserGroupsSettingEnabled()) {
            $samlUserGroupsSetting = $this->config->getUserGroupsSetting();
            if (isset($samlUserGroupsSetting['attribute']) && $samlUserGroupsSetting['attribute'] != ''
                && isset($attributes[$samlUserGroupsSetting['attribute']])) {
                $this->synchronizeAclGroups($login, $attributes[$samlUserGroupsSetting['attribute']], $samlUserGroupsSetting);
            }
        }

        $_SESSION['samlUserdata'] = $auth->getAttributes();
        $_SESSION['IdPSessionIndex'] = $auth->getSessionIndex();
        $_SESSION['samlNameId'] = $auth->getNameId();
        $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
        $_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
        $_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();

        // we should store the correspondance between SAML session and PHP Session
        // for logout by the IdP
        $this->initCacheProfile();
        \jCache::set('saml/session/'.$auth->getSessionIndex(), session_id(), null, 'saml');

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

    protected function synchronizeAclGroups($login, $newGroups, $samlUserGroupsSetting)
    {
        // Get user groups provided by SAML
        // if it is a string, split the value to retrieve a list
        if (!is_array($newGroups)) {
            $separator = ',';
            if (isset($samlUserGroupsSetting['separator']) && $samlUserGroupsSetting['separator'] != '') {
                $separator = preg_quote($samlUserGroupsSetting['separator']);
            }
            $newGroups = preg_split("/\\s*".$separator."\\s*/", $newGroups);
        }

        // Get all groups
        $allGroups = iterator_to_array(\jAcl2DbUserGroup::getGroupList());
        $allGroups = array_map(
            function($g) {
                return $g->id_aclgrp;
            },
            $allGroups
        );

        // Get login groups without private or default group to keep them
        $currentGroups =[];
        foreach(\jAcl2DbUserGroup::getGroupList($login) as $g) {
            if ($g->grouptype != \jAcl2DbUserGroup::GROUPTYPE_NORMAL) {
                continue;
            }
            $currentGroups[] = $g->id_aclgrp;
        }

        // Update user groups if prefix is defined
        if (isset($samlUserGroupsSetting['prefix']) && $samlUserGroupsSetting['prefix'] != '') {
            $prefix = $samlUserGroupsSetting['prefix'];
            $newGroups = array_filter(
                $newGroups,
                function($g) use ($prefix) {
                    return strpos($g, $prefix) === 0;
                }
            );
            if (isset($samlUserGroupsSetting['drop_prefix']) && $samlUserGroupsSetting['drop_prefix']) {
                $newGroups = array_map(
                    function($g) use ($prefix) {
                        return substr($g, strlen($prefix));
                    },
                    $newGroups
                );
            }
        }

        // Filter user groups against all groups
        // Remove user groups provided by SAML not in the application
        $newGroups = array_filter($newGroups,function($g) use ($allGroups) {
            return array_search($g, $allGroups);
        });

        // Get the list of login groups not in user groups to remove them
        $groupsToRemove = array_filter($currentGroups, function($g) use ($newGroups) {
            return array_search($g, $newGroups) === false;
        });

        // Get the list of user groups not in login groups to add them
        $groupsToAdd = array_filter($newGroups, function($g) use ($currentGroups) {
            return array_search($g, $currentGroups) === false;
        });

        // Update
        if (count($groupsToRemove) != 0 || count($groupsToAdd) != 0) {
            foreach($groupsToRemove as $grpId) {
                \jAcl2DbUserGroup::removeUserFromGroup($login, $grpId);
            }

            foreach($groupsToAdd as $grpId) {
                \jAcl2DbUserGroup::addUserToGroup($login, $grpId);
            }

            \jAcl2::clearCache();
        }
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

        if (isset($_GET['SAMLRequest'])) {
            // this a logout request from the idp, not from the application
            // we must logout from the right PHP session

            // we don't have a way to retrieve the SAML session index from the
            // $auth object, so, let's get by ourselves
            $samlRequest = new LogoutRequest($this->config->getSettings(), $_GET['SAMLRequest']);
            $samlSessions = LogoutRequest::getSessionIndexes($samlRequest->getXML());
            $originalSessionId = session_id();
            $this->initCacheProfile();

            session_commit();
            foreach($samlSessions as $samlSessionId) {

                $phpSessionToDelete = \jCache::get('saml/session/'.$samlSessionId, 'saml');
                if (!$phpSessionToDelete) {
                    continue;
                }
                // hijack the session to destroy
                session_id($phpSessionToDelete);
                session_start();
                \jAuth::logout();

                unset($_SESSION['samlUserdata']);
                unset($_SESSION['IdPSessionIndex']);
                unset($_SESSION['samlNameId']);
                unset($_SESSION['samlNameIdFormat']);
                unset($_SESSION['samlNameIdNameQualifier']);
                unset($_SESSION['samlNameIdSPNameQualifier']);

                if (!isset($this->authConfig['session_destroy'])
                    || $this->authConfig['session_destroy'] == ''
                ) {
                    session_destroy();
                }
                session_commit();
            }

            // restore current session
            session_id($originalSessionId);
            session_start();
        }
        else {
            \jAuth::logout();
            unset($_SESSION);
        }

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
