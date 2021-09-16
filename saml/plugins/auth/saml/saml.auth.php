<?php
/**
* @author     Laurent Jouanneau
* @copyright  2019 3liz
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/


/**
* authentification driver for authentification with a SAML server
*/
class samlAuthDriver extends jAuthDriverBase implements jIAuthDriver2 {

    protected $automaticAccountCreation = false;

    /**
     * @var bool true if the current login process is made with SAML
     */
    protected $authWithSamlActivated = false;

    function __construct($params) {
        if (property_exists(jApp::config(), 'saml') && is_array(jApp::config()->saml)) {
            $params = array_merge($params, jApp::config()->saml);
        }
        parent::__construct($params);
        if (!isset($this->_params['profile'])) {
            $this->_params['profile'] = '';
        }
        if (!isset($this->_params['dao'])) {
            throw new Exception("Dao selector is missing into the saml configuration");
        }

        if (isset($this->_params['automaticAccountCreation'])) {
            $this->automaticAccountCreation = $this->_params['automaticAccountCreation'];
        }
    }

    public function saveNewUser($user) {
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        $dao->insert($user);
        return true;
    }

    public function removeUser($login) {
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        if (function_exists('mb_strtolower')) {
            $login = mb_strtolower($login);
        }
        $dao->deleteByLogin($login);
        return true;
    }

    public function updateUser($user) {
        if (!is_object($user)) {
            throw new jException('saml~auth.error.object.user.unknown');
        }

        if ($user->login == '') {
            throw new jException('saml~auth.error.user.login.unset');
        }

        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        $dao->update($user);
        return true;
    }

    public function getUser($login) {
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        if (function_exists('mb_strtolower')) {
            $login = mb_strtolower($login);
        }
        return $dao->getByLogin($login);
    }

    public function createUserObject($login, $password) {
        $user = jDao::createRecord($this->_params['dao'], $this->_params['profile']);
        $user->login = $login;
        $user->password = $this->cryptPassword($password);
        return $user;
    }

    public function getUserList($pattern) {
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        if ($pattern == '%' || $pattern == '') {
            return $dao->findAll();
        } else {
            return $dao->findByLogin($pattern);
        }
    }

    public function canChangePassword($login) {
        return true;
    }

    public function changePassword($login, $newpassword) {
        $dao = jDao::get($this->_params['dao'], $this->_params['profile']);
        return $dao->updatePassword($login, $this->cryptPassword($newpassword));
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

    public function verifyPassword($login, $password)
    {
        $daouser = jDao::get($this->_params['dao'], $this->_params['profile']);
        $user = $daouser->getByLogin($login);

        if ($this->authWithSamlActivated) {
            // authentication with SAML

            if ($this->currentPassword != $password) {
                return false;
            }
            $this->currentPassword = '';
            $this->authWithSamlActivated = false;

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
                } else {
                    return false;
                }
            }
        }
        else {
            // authentication with login/password
            if (!$user) {
                return false;
            }
            $result = $this->checkPassword($password, $user->password);
            if ($result === false)
                return false;

            if ($result !== true) {
                // it is a new hash for the password, let's update it persistently
                $user->password = $result;
                $daouser->updatePassword($login, $result);
            }
        }
        return $user;
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
