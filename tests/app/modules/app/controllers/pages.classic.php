<?php
/**
 * @package   app
 * @subpackage app
 * @author    Laurent Jouanneau
 * @copyright 2019 3liz
 * @link      https://3liz.com
 * @license    All rights reserved
 */

class pagesCtrl extends jController {

    public $pluginParams = array(
        '*'=>array('auth.required'=>true)
    );


    /**
     *
     */
    function profile() {
        $rep = $this->getResponse('html');

        $attributes = array();

        $tpl = new jTpl();
        $tpl->assign('attributes', $attributes);

        $rep->body->assign('MAIN', $tpl->fetch('profile'));

        return $rep;
    }




}
