<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2026 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;



use Jelix\Authentication\Core\AuthSession\AuthUser;
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
     * @param Configuration  $configuration
     */
    function __construct(Configuration $configuration)
    {
        $this->config = $configuration;
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

    /**
     * Start SAML Login process
     *
     * @param string $returnUrl the url where to redirect after SAML authentication
     * @return string|null the url to redirect to for authentication
     * @throws Error
     */
    function startLoginProcess($returnUrl)
    {
        $auth = new Auth($this->config->getSettingsArray());

        return $auth->login($returnUrl, array(), false, false, true);
    }

    /**
     * @param  \jClassicRequest|\jRequest  $request
     *
     * @return AuthUser
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
        $samlAttributes = $auth->getAttributes();
        if (empty($samlAttributes)) {
            throw new LoginException(
                \jLocale::get('saml~auth.authentication.error.saml.attributes.missing', array($loginAttr)),
                self::ACS_ERR_ATTR_MISSING
            );
        }

        if (!isset($samlAttributes[$loginAttr])) {
            throw new LoginException(
                \jLocale::get('saml~auth.authentication.error.saml.attribute.missing', array($loginAttr)),
                self::ACS_ERR_ATTR_MISSING
            );
        }

        $login = $samlAttributes[$loginAttr];
        if (is_array($login)) {
            $login = $login[0];
        }

        $relayState = $request->getParam('RelayState');
        if (!$request->isPostMethod()
            || $relayState == ''
            || $relayState == \jUrl::getFull('saml~endpoint:acs')
        ) {
            // FIXME JelixAuthentication should provides a method to retrieve the
            // url of the default page after a login
            // home page
            $relayState = $request->getServerURI() . \jApp::urlBasePath();
        }

        $userAttributes = array(
            AuthUser::ATTR_LOGIN => $login,
            'samlSession' => array(
                'samlUserdata' => $samlAttributes,
                'accountAttributes' => $this->readAttributesMapping($samlAttributes),
                'IdPSessionIndex' => $auth->getSessionIndex(),
                'samlNameId' => $auth->getNameId(),
                'samlNameIdFormat' => $auth->getNameIdFormat(),
                'samlNameIdNameQualifier' => $auth->getNameIdNameQualifier(),
                'samlNameIdSPNameQualifier' => $auth->getNameIdSPNameQualifier(),
                'relayState' => $relayState
            )

        );

        $user = new AuthUser($login, $userAttributes);
        return $user;
    }

    protected function readAttributesMapping($samlAttributes)
    {
        $accountAttributes = array();
        $mappingAttributes = $this->config->getAttributesMapping();
        foreach($mappingAttributes as $property => $attribute) {
            if (!isset($samlAttributes[$attribute]) || !$samlAttributes[$attribute]) {
                continue;
            }
            $val = $samlAttributes[$attribute];
            if (is_array($val)) {
                $val = $val[0];
            }
            $accountAttributes[$property] = $val;
        }
        return $accountAttributes;
    }

    public function finishLoginProcess(AuthUser $user)
    {

        $login = $user->getLogin();

        if ($this->config->isUserGroupsSettingEnabled()) {
            $samlUserGroupsSetting = $this->config->getUserGroupsSetting();
            if (isset($samlUserGroupsSetting['attribute']) && $samlUserGroupsSetting['attribute'] != ''
                && isset($samlAttributes[$samlUserGroupsSetting['attribute']])) {
                $this->synchronizeAclGroups($login, $samlAttributes[$samlUserGroupsSetting['attribute']], $samlUserGroupsSetting);
            }
        }

        $samlSession = $user->getAttribute('samlSession');
        // we should store the correspondance between SAML session and PHP Session
        // for logout by the IdP
        $this->initCacheProfile();
        $sessIndex = $samlSession['IdPSessionIndex'];
        \jCache::set('saml/session/'.$sessIndex, session_id(), null, 'saml');
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

        // Keep only new groups having a prefix, if a prefix is defined
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
     * @param AuthUser $authUser
     * @param string $defaultRelayState
     *
     * @return string the url to redirect to
     * @throws Error
     * @throws \jException
     */
    function startLogoutProcess($authUser, $defaultRelayState)
    {
        $samlSettings = $this->config->getSettingsArray();
        $auth = new Auth($samlSettings);

        $sessionIndex = null;
        $nameId = null;
        $nameIdFormat = null;
        $nameIdNameQualifier = null;
        $nameIdSPNameQualifier = null;

        $hasSAMLSession = false;

        if ($authUser && $samlSession = $authUser->getAttribute('samlSession')) {
            if (isset($samlSession['IdPSessionIndex']) && !empty($samlSession['IdPSessionIndex'])) {
                $sessionIndex = $_SESSION['IdPSessionIndex'];
                $hasSAMLSession = true;
            }
            if (isset($samlSession['samlNameId'])) {
                $nameId = $samlSession['samlNameId'];
                $hasSAMLSession = true;
            }
            if (isset($samlSession['samlNameIdFormat'])) {
                $nameIdFormat = $samlSession['samlNameIdFormat'];
            }
            if (isset($samlSession['samlNameIdNameQualifier'])) {
                $nameIdNameQualifier = $samlSession['samlNameIdNameQualifier'];
            }
            if (isset($samlSession['samlNameIdSPNameQualifier'])) {
                $nameIdSPNameQualifier = $samlSession['samlNameIdSPNameQualifier'];
            }
        }

        // FIXME JelixAuthentication should provides a method to get the default page
        // after a logout
        // home page
        $relayState = $defaultRelayState;

        if (!$hasSAMLSession) {
            // to avoid error "unknown session" on the IdP side
            return $relayState;
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
                \jAuthentication::session()->unsetSessionUser();
                session_commit();
            }

            // restore current session
            session_id($originalSessionId);
            session_start();
        }
        else {
            \jAuthentication::session()->unsetSessionUser();
            unset($_SESSION);
        }

        if ($url) {
            return $url;
        }
        $relayState = $request->getParam('RelayState');
        if ($relayState) {
            return $relayState;
        }
        // FIXME JelixAuthentication should provides a method to get the default page
        // after a logout
        return $request->getServerURI() . \jApp::urlBasePath();
    }

    static function logError($error, $context= '')
    {
        $file = \jApp::logPath().'/saml.log';
        $date = date('Y-m-d H:i:s');
        $msg = $date.';'.$context.';'.$error."\n";
        file_put_contents($file, $msg, FILE_APPEND);
    }

    static function getErrorsLog()
    {
        $file = \jApp::logPath().'/saml.log';
        $errors = file_get_contents($file);
        return $errors;
    }

    static function cleanErrorsLog()
    {
        $file = \jApp::logPath().'/saml.log';
        file_put_contents($file, '');
    }
}
