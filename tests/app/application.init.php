<?php
/**
* @package   app
* @subpackage
* @author    Laurent Jouanneau
* @copyright 2019 3liz
* @link      https://3liz.com
* @license    All rights reserved
*/

require (__DIR__.'/vendor/autoload.php');
jApp::initPaths(
    __DIR__.'/',
    __DIR__.'/www/',
    __DIR__.'/var/',
    __DIR__.'/var/log/',
    __DIR__.'/var/config/',
    __DIR__.'/scripts/'
);
jApp::setTempBasePath(realpath(__DIR__.'/temp/').'/');
require (__DIR__.'/vendor/jelix_app_path.php');

jApp::declareModulesDir(array(
                        __DIR__.'/modules/'
                    ));
jApp::declarePluginsDir(array(
                        __DIR__.'/plugins'
                    ));