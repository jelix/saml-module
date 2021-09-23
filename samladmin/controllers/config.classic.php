<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class configCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );


    /**
     * Display a summary of the information taken from the ~ configuration file.
     */
    public function index()
    {
        $rep = $this->getResponse('html');

        $tpl = new jTpl();
        $rep->body->assign('MAIN', $tpl->fetch('config'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');

        return $rep;
    }

}
