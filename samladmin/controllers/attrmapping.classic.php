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
     * @param \Jelix\Saml\Configuration $config
     */
    protected function setupForm($form, $config)
    {
        /** @var jFormsControlGroup $groupCtrl */
        $groupCtrl = $form->getControl('attrsgroup');

        $defaultValues = $config->getAttributesMapping();
        $userFormSelector = jAuth::getDriverParam('form');
        $authForm = jForms::create($userFormSelector);
        foreach ($authForm->getControls() as $ctrlName => $ctrl) {
            if (! ($ctrl instanceof jFormsControlInput)) {
                continue;
            }

            $attrCtrl = new jFormsControlInput('attr_'.$ctrl->ref);
            $attrCtrl->label = $ctrl->label;
            $attrCtrl->defaultValue = $defaultValues[$ctrl->ref] ?: '';
            $groupCtrl->addChildControl($attrCtrl);
        }
        $form->addControl($groupCtrl);
        jForms::destroy($userFormSelector);

    }


    public function initform()
    {
        $config = new \Jelix\Saml\Configuration(jApp::config(), false);
        $form = jForms::create('attrmapping');
        $this->setupForm($form, $config);

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
        $config = new \Jelix\Saml\Configuration(jApp::config(), false);
        $this->setupForm($form, $config);

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
        $config = new \Jelix\Saml\Configuration(jApp::config(), false);
        $this->setupForm($form, $config);

        $form->initFromRequest();
        if (!$form->check()) {
            $rep->action = 'samladmin~attrmapping:edit';
            return $rep;
        }

        $config = new \Jelix\Saml\ConfigurationModifier();
        $config->setSAMLAttributeForLogin($form->getData('login'));

        /** @var jFormsControlGroup $groupCtrl */
        $groupCtrl = $form->getControl('attrsgroup');
        $mapping = array();
        foreach($groupCtrl->getChildControls() as $ctrl) {
            if (!preg_match('/^attr_(.+)$/', $ctrl->ref, $m)) {
                continue;
            }
            $daoAttr = $m[1];
            $samlAttr = $form->getData($ctrl->ref);
            if ($samlAttr) {
                $mapping[$daoAttr] = $samlAttr;
            }
        }
        $config->setAttributesMapping($mapping);
        $config->save();
        jForms::destroy('attrmapping');
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }
}
