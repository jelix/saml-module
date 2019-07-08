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

        $attributes = $_SESSION['samlUserdata'];

        $rep->title = jLocale::get('saml~auth.authentication.done');

        $tpl = new jTpl();
        $tpl->assign('attributes', $attributes);
        $tpl->assign('session', var_export($_SESSION, true));
        $rep->body->assign('MAIN', $tpl->fetch('profile'));
        return $rep;
    }




}
