<?php
/**
* @package   app
* @subpackage 
* @author    your name
* @copyright 2019 3liz
* @link      https://3liz.com
* @license    All rights reserved
*/

require ('../application.init.php');
require (JELIX_LIB_CORE_PATH.'request/jClassicRequest.class.php');

checkAppOpened();

jApp::loadConfig('admin/config.ini.php');

jApp::setCoord(new jCoordinator());
jApp::coord()->process(new jClassicRequest());



