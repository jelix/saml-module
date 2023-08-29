<?php
/**
* @author  Laurent Jouanneau
* @copyright  2019-2021 3liz
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
require_once(JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');

/**
 * the plugin for the coordinator, that checks authentication at each page call
 */
class samlCoordPlugin extends AuthCoordPlugin {

    public function beforeAction ($params)
    {
        $currentAction = jApp::coord()->originalAction;
        if (
            (
                $currentAction->module == 'jauth' &&
                $currentAction->controller == 'login' &&
                $currentAction->method == 'out'
            ) || (
                $currentAction->module == 'jcommunity' &&
                $currentAction->controller == 'login' &&
                $currentAction->method == 'out'
            )
        ) {
            if (
                isset($_SESSION['samlUserdata']) &&
                isset($_SESSION['samlNameId']) &&
                isset($_SESSION['IdPSessionIndex'])
            ) {
                // if the user is in a SAML session, logout with SAML
                $selector = new jSelectorAct('saml~auth:logout');
                return $selector;
            }
        }

        if (!isset($_SESSION[$this->config['session_name']])) {
            $notLogged = true;
        } else {
            $notLogged = !jAuth::isConnected();
        }
        $needAuth = isset($params['auth.required']) ? ($params['auth.required'] == true) : $this->config['auth_required'];

        if ($needAuth && $notLogged) {
            $samlConf = jApp::config()->saml;
            if (isset($samlConf['forceSAMLAuthOnPrivatePage']) && $samlConf['forceSAMLAuthOnPrivatePage']) {
                return new jSelectorAct('saml~auth:login');
            }
        }

        return parent::beforeAction($params);
    }
}
