<?php
/**
* @package   app
* @subpackage app
* @author    Laurent Jouanneau
* @copyright 2019 3liz
* @link      https://3liz.com
* @license    All rights reserved
*/

class defaultCtrl extends jController {

    public $pluginParams = array(
        '*'=>array('auth.required'=>false)
    );


    /**
    *
    */
    function index() {
        $rep = $this->getResponse('html');

        $tpl = new jTpl();

        $rep->body->assign('MAIN', $tpl->fetch('index'));

        return $rep;
    }




}
