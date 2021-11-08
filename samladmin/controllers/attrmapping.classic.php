<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

class attrmappingCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );

    /**
     * @param jFormsBase $form
     */
    protected function setupForm($form)
    {

    }


    public function initform()
    {
        $config = new \Jelix\Saml\Configuration(jApp::config(), false);
        $form = jForms::create('attrmapping');
        $this->setupForm($form);

        $form->setData('login', $config->getSAMLAttributeForLogin());
        $rep = $this->getResponse('redirect');
        $rep->action = 'samladmin~attrmapping:edit';
        return $rep;
    }


    public function edit()
    {
        $rep = $this->getResponse('html');
        $form = jForms::get('attrmapping');
        if (!$form) {
            $rep = $this->getResponse('redirect');
            $rep->action = 'samladmin~config:index';
            return $rep;
        }
        $this->setupForm($form);

        $tpl = new jTpl();
        $tpl->assign('attrform', $form);
        //$rep->addJSLink(jUrl::get('samladmin~config:asset', array('file'=>'sp.js')));
        $rep->body->assign('MAIN', $tpl->fetch('attrmapping'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');
        return $rep;
    }


    function save()
    {
        $rep = $this->getResponse('redirect');

        $form = jForms::get('attrmapping');
        if (!$form) {
            jMessage::add('missing form', 'error');
            $rep->action = 'samladmin~config:index';
            return $rep;
        }
        $form->initFromRequest();
        if (!$form->check()) {
            $rep->action = 'samladmin~attrmapping:edit';
            return $rep;
        }

        $config = new \Jelix\Saml\ConfigurationModifier();
        $config->setSAMLAttributeForLogin($form->getData('login'));

        $config->save();
        jForms::destroy('attrmapping');
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }
}
