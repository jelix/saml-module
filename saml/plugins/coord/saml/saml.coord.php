<?php
/**
* @author  Laurent Jouanneau
* @copyright  2019 3liz
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/

require(JELIX_LIB_PATH.'auth/jAuth.class.php');
require(JELIX_LIB_PATH.'auth/jAuthDummyUser.class.php');


/**
* the plugin for the coordinator, that checks authentication at each page call
*/
class samlCoordPlugin implements jICoordPlugin {

    // keep public for jAuth :/
    public $config;

    function __construct($conf){
        $this->config = $conf;

        if (!isset($this->config['session_name'])
            || $this->config['session_name'] == ''){
            $this->config['session_name'] = 'JELIX_USER';
        }

    }

    /**
     * @param    array  $params   plugin parameters for the current action
     * @return null or jSelectorAct  if action should change
     */
    public function beforeAction ($params) {
        $notLogged = false;
        $selector = null;

        //Creating the user's object if needed
        if (! isset ($_SESSION[$this->config['session_name']])){
            $notLogged = true;
            $_SESSION[$this->config['session_name']] = new jAuthDummyUser();
        }else{
            $notLogged = ! jAuth::isConnected();
        }
        
        try {
            $needAuth = isset($params['auth.required']) ? ($params['auth.required']==true):$this->config['auth_required'];

            if ($needAuth) {
                // if this is an ajax request, we don't want redirection to a web page
                // so we shouldn't force authentication if we are not logged
                if ($notLogged && jApp::coord()->request->isAjax()) {
                    throw new jException($this->config['error_message']);
                }

                // execute the login action, except if the request is
                // for the endpoint (login response from the identity server)
                $currentAction = jApp::coord()->originalAction;
                if ($currentAction->module != 'saml' ||
                    $currentAction->controller != 'endpoint'
                ) {
                    $selector = new jSelectorAct('saml~auth:login');
                }
            }
        }
        catch(Exception $error) {
            $selector = new jSelectorAct($this->config['on_error_action']);
        }
        
        return $selector;
    }


    public function beforeOutput(){}

    public function afterProcess (){}

}
