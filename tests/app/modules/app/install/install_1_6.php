<?php
/**
* @package   app
* @subpackage app
* @author    Laurent Jouanneau
* @copyright 2019 3liz
* @link      https://3liz.com
* @license    All rights reserved
*/


class appModuleInstaller extends jInstallerModule {

    function install() {
        //if ($this->firstDbExec())
        //    $this->execSQLScript('sql/install');

        /*if ($this->firstExec('acl2')) {
            jAcl2DbManager::addSubject('my.subject', 'app~acl.my.subject', 'subject.group.id');
            jAcl2DbManager::addRight('admins', 'my.subject'); // for admin group
        }
        */
    }
}