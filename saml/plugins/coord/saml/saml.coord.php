<?php
/**
* @author  Laurent Jouanneau
* @copyright  2019 3liz
* @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
*/
require(JELIX_LIB_PATH.'plugins/coord/auth/auth.coord.php');

require(JELIX_LIB_PATH.'auth/jAuth.class.php');
require(JELIX_LIB_PATH.'auth/jAuthDummyUser.class.php');


/**
 * the plugin for the coordinator, that checks authentication at each page call
 * @deprecated this plugin exists only to ease upgrade from the 1.0 version of
 * the module
 */
class samlCoordPlugin extends AuthCoordPlugin {


}
