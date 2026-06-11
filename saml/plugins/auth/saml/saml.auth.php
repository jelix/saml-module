<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019-2026 3liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */


/**
 * authentification driver for authentification with a SAML server
 */
class samlAuthDriver extends jAuthDriverBase implements jIAuthDriver3 {

    protected $automaticAccountCreation = false;

    /**
     * @var bool true if the current login process is made with SAML
     */
    protected $authWithSamlActivated = false;

    protected $loginsCache = array();

    protected $caseSensitiveLogins = false;

    protected $dbProfile = '';

    function __construct($params)
    {
        if (property_exists(jApp::config(), 'saml') && is_array(jApp::config()->saml)) {
            $params = array_merge($params, jApp::config()->saml);
        }
        parent::__construct($params);
        $this->dbProfile = $params['dbProfile'] ?: '';
        if (!isset($this->_params['dao'])) {
            throw new Exception("Dao selector is missing into the saml configuration");
        }

        if (isset($this->_params['automaticAccountCreation'])) {
            $this->automaticAccountCreation = $this->_params['automaticAccountCreation'];
        }

        if (isset($this->_params['caseSensitiveLogins'])) {
            $this->caseSensitiveLogins = $this->_params['caseSensitiveLogins'];
        }
    }

    /**
     * Return the dao used to stored User account data
     * @return jDaoFactoryBase
     */
    public function getAccountDao()
    {
        return jDao::get($this->_params['dao'], $this->dbProfile);
    }

    /**
     * Return the dao used to store specific SAML data of the user
     * @return jDaoFactoryBase
     */
    protected function getSamlAccountDao()
    {
        return jDao::get('saml~saml_account', $this->dbProfile);
    }

    /**
     * @param jDaoRecordBase $user
     * @return true
     */
    public function saveNewUser($user)
    {
        $this->getAccountDao()->insert($user);

        if ($this->authWithSamlActivated) {
            $this->createSAMLAccount($user);
        }

        return true;
    }

    /**
     * @param jDaoRecordBase $user
     */
    protected function createSAMLAccount($user)
    {
        $samlAccount = jDao::createRecord('saml~saml_account', $this->dbProfile);
        $samlAccount->login = $user->login;
        $samlAccount->firstUsed = date('Y-m-d H:i:s');
        $samlAccount->lastUsed = date('Y-m-d H:i:s');
        $samlAccount->usageCount = 1;
        $samlAccount->samlData = json_encode($this->samlAttributesValues);
        $this->getSamlAccountDao()->insert($samlAccount);
    }

    public function removeUser($login)
    {
        $dao = $this->getAccountDao();
        $daoSaml = $this->getSamlAccountDao();
        // retrieve the real login, in the case where the given login does not match character case
        $user = $this->getUser($login);
        if (!$user) {
            return false;
        }

        if ($this->caseSensitiveLogins && function_exists('mb_strtolower')) {
            $loginLegacy = mb_strtolower($login);
            if ($loginLegacy != $login) {
                // compatibility with an old version of the module, when
                // logins were stored only in lowercase
                $daoSaml->delete($loginLegacy);
                $dao->deleteByLogin($loginLegacy);
            }
        }

        $daoSaml->delete($user->login);
        $dao->deleteByLogin($user->login);
        unset($this->loginsCache[$login]);
        return true;
    }

    /**
     * @param jDaoRecordBase $user
     */
    public function updateUser($user)
    {
        if (!is_object($user)) {
            throw new jException('saml~auth.error.object.user.unknown');
        }

        if ($user->login == '') {
            throw new jException('saml~auth.error.user.login.unset');
        }

        $this->getAccountDao()->update($user);
        return true;
    }

    /**
     * Retrieve the jAuth account with case-insensitive matching
     *
     * @param $login
     * @return jDaoRecordBase|null
     */
    public function getUser($login)
    {
        if (isset($this->loginsCache[$login])) {
            return $this->loginsCache[$login];
        }
        $dao = $this->getAccountDao();

        if ($this->caseSensitiveLogins) {
            $user = $dao->getByLogin($login);
            if (!$user && function_exists('mb_strtolower')) {
                // compatibility with an old version of the module, when
                // logins were stored only in lowercase
                $loginLegacy = mb_strtolower($login);
                if ($loginLegacy != $login) {
                    $user = $dao->getByLogin($loginLegacy);
                }
            }
            if ($user) {
                $this->loginsCache[$login] = $user;
            }
            return $user;
        }

        if (function_exists('mb_strtolower')) {
            $llogin = mb_strtolower($login);
        }
        else {
            $llogin = strtolower($login);
        }
        $conditions = new jDaoConditions();
        $conditions->addCondition('login', '=', $llogin, 'lower(%s)');
        $rs = $dao->findBy($conditions);

        // IDP may return a login that have a different character case than the login of a previous
        // authentication, so previous versions of the module may create several accounts with the
        // same login having different character case.
        // So, if there are multiple account with the same login having different character case,
        // we return the one which match exactly the given login, else we return another one.
        $alternateUser = null;
        foreach ($rs as $rec) {
            if ($rec->login == $login) {
                $this->loginsCache[$login] = $rec;
                return $rec;
            }
            else if ($alternateUser == null) {
                $alternateUser = $rec;
            }
        }
        if ($alternateUser) {
            $this->loginsCache[$login] = $alternateUser;
        }
        return $alternateUser;
    }

    public function createUserObject($login, $password)
    {
        $user = jDao::createRecord($this->_params['dao'], $this->dbProfile);
        $user->login = $login;
        $user->password = $this->cryptPassword($password);
        return $user;
    }

    public function getUserList($pattern)
    {
        $dao = $this->getAccountDao();
        if ($pattern == '%' || $pattern == '') {
            return $dao->findAll();
        } else {
            return $dao->findByLogin($pattern);
        }
    }

    protected $reasonToForbiddenPasswordChange = '';

    public function canChangePassword($login)
    {
        $user = $this->getUser($login);
        $can = false;
        if ($user) {
            $can = $this->canUseLocalPassword($user->login);
        }

        if (!$can) {
            $this->reasonToForbiddenPasswordChange = jLocale::get('saml~auth.localpassword.error');
        }
        else {
            $this->reasonToForbiddenPasswordChange = '';
        }
        return $can;
    }

    public function getReasonToForbiddenPasswordChange()
    {
        return $this->reasonToForbiddenPasswordChange;
    }


    public function changePassword($login, $newpassword)
    {
        $user = $this->getUser($login);
        if ($user) {
            return $this->getAccountDao()->updatePassword($user->login, $this->cryptPassword($newpassword));
        }
        return false;
    }

    /**
     * Call it before calling jAuth::login() when authentication
     * with SAML is a success.
     *
     * @return string random password to give to jAuth::login()
     */
    public function activateAuthWithSaml()
    {
        $this->authWithSamlActivated = true;
        $this->currentPassword = jAuth::getRandomPassword();
        return $this->currentPassword;
    }

    /**
     * Indicate, during a login process, if the user is using SAML or not
     *
     * It can be useful for authentication listeners, to process SAML attributes
     * for example.
     *
     * The returned value is irrelevant outside a login process.
     *
     * @return bool
     */
    public function isSAMLAuthUsedForTheCurrentLoginProcess()
    {
        return $this->authWithSamlActivated;
    }

    public function verifyPassword($login, $password)
    {
        $daouser = $this->getAccountDao();
        $user = $this->getUser($login);

        if ($this->authWithSamlActivated) {
            // authentication with SAML

            if ($this->currentPassword != $password) {
                return false;
            }
            $this->currentPassword = '';

            if (!$user) {
                if ($this->automaticAccountCreation) {
                    // authentication with SAML
                    $user = $this->createUserObject($login, $password);
                    if (jApp::isModuleEnabled('jcommunity')) {
                        $user->status = 1; // STATUS_VALID
                    }
                    foreach($this->userAttributesValues as $property => $value) {
                        $user->$property = $value;
                    }
                    jAuth::saveNewUser($user);
                }
            }
            else {
                // update saml account data
                $daoSaml = $this->getSamlAccountDao();
                $samlAccount = $daoSaml->get($user->login);
                if ($samlAccount) {
                    $samlAccount->lastUsed = date('Y-m-d H:i:s');
                    $samlAccount->usageCount++;
                    $samlAccount->samlData = json_encode($this->samlAttributesValues);
                    $daoSaml->update($samlAccount);
                }
                else {
                    $this->createSAMLAccount($user);
                }
            }
        }
        else {
            // authentication with login/password
            if (!$user) {
                return false;
            }

            if (!$this->canUseLocalPassword($user->login)) {
                return false;
            }

            $result = $this->checkPassword($password, $user->password);
            if ($result === false)
                return false;

            if ($result !== true) {
                // it is a new hash for the password, let's update it persistently
                $user->password = $result;
                $daouser->updatePassword($user->login, $result);
            }
        }
        return $user;
    }


    protected function canUseLocalPassword($login)
    {
        $config = jApp::config()->saml;
        if (isset($config['allowSAMLAccountToUseLocalPassword']) && $config['allowSAMLAccountToUseLocalPassword']) {
            return true;
        }

        // if the user has no SAML account, it can log with its local password
        $samlAccount = $this->getSamlAccountDao()->get($login);
        if (!$samlAccount) {
            return true;
        }

        // if the user has the right to administrate SAML configuration, it can login with local password
        if (jAcl2::checkByUser($login, 'saml.config.access')) {
            return true;
        }
        return false;
    }

    const AUTH_NOT_ALLOWED = 0;
    const AUTH_SAML_ALLOWED = 2;
    const AUTH_PASSWORD_ALLOWED = 4;

    public function getAuthenticationPermissions($login)
    {
        $perms = self::AUTH_NOT_ALLOWED;
        $user = $this->getUser($login);
        if (!$user) {
            return $perms;
        }
        $samlAccount = $this->getSamlAccountDao()->get($user->login);
        if ($samlAccount) {
            $perms |= self::AUTH_SAML_ALLOWED;
        }
        else {
            // if the user has the right to administrate SAML configuration, it can login with local password
            if (jAcl2::checkByUser($user->login, 'saml.config.access')) {
                $perms |= self::AUTH_PASSWORD_ALLOWED;
            }
        }

        $config = jApp::config()->saml;
        if (isset($config['allowSAMLAccountToUseLocalPassword']) && $config['allowSAMLAccountToUseLocalPassword']) {
            $perms |= self::AUTH_PASSWORD_ALLOWED;
        }

        return $perms;
    }

    protected $samlAttributesValues = array();

    protected $userAttributesValues = array();

    public function setAttributesMapping($samlAttributes, $mappingAttributes) {

        $this->samlAttributesValues = $samlAttributes;

        foreach($mappingAttributes as $property => $attribute) {
            if (!isset($samlAttributes[$attribute]) || !$samlAttributes[$attribute]) {
                continue;
            }
            $val = $samlAttributes[$attribute];
            if (is_array($val)) {
                $val = $val[0];
            }
            $this->userAttributesValues[$property] = $val;
        }
    }

    public function getSAMLAttributes() {
        return $this->samlAttributesValues;
    }

    public function getUserAttributes() {
        return $this->userAttributesValues;
    }


}
