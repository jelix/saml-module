<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021-2026 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\Core\IdentityAttributes;

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

        $accountAdapter = jAuthentication::accountAdapter();
        if ($accountAdapter) {
            $accountClaims = $accountAdapter->getSupportedAccountClaims();
        }
        else {
            $accountClaims = AuthUser::getDefaultSupportedAccountClaims();
        }

        $labels = IdentityAttributes::getAttributeLabels($accountClaims);

        $listOfField = array();

        // create a field into the settings form for each field of the user form
        // that is listed into the labels array
        foreach ($labels as $idAttr => $label) {

            $listOfField[] = $idAttr;

            $attrCtrl = new jFormsControlInput('attr_'.$idAttr);
            $attrCtrl->label = $label;
            $attrCtrl->defaultValue = $defaultValues[$idAttr] ?? '';

            if ($idAttr == 'email'
                || $idAttr == IdentityAttributes::Email
                || $idAttr == 'username'
                || $idAttr == IdentityAttributes::UserName
            ) {
                $attrCtrl->required = true;
            }
            $groupCtrl->addChildControl($attrCtrl);
        }
        $form->addControl($groupCtrl);

        if ($config->mustRedirectToSAMLAuthOnLoginPage()) {
            $ctrl = $form->getControl('forceSAMLAuthOnLoginPage');
            $ctrl->label = jLocale::get('samladmin~admin.attrmapping.form.forceSAMLAuthOnLoginPage.redirectlabel');
        }

        return $listOfField;
    }


    public function initform()
    {
        $config = new \Jelix\Saml\Configuration(false);
        $form = jForms::create('attrmapping');
        $this->setupForm($form, $config);

        $userGroupsSetting = $config->getUserGroupsSetting();
        if (isset($userGroupsSetting['enabled']) && $userGroupsSetting['enabled']) {
            $form->setData('groupsfromsaml', $userGroupsSetting['enabled']);
            $form->setData('groupsfromsamlattr', $userGroupsSetting['attribute'] ?? '');
            $form->setData('groupsfromsamlprefix', $userGroupsSetting['prefix'] ?? '');
            $form->setData('groupsfromsamldropprefix', $userGroupsSetting['drop_prefix']);
        }

        $form->setData('login', $config->getSAMLAttributeForLogin());
        $form->setData('automaticAccountCreation', $config->isAutomaticAccountCreation());
        $form->setData('allowSAMLAccountToUseLocalPassword', $config->isAllowingSAMLAccountToUseLocalPassword());
        $form->setData('forceSAMLAuthOnPrivatePage', $config->mustForceSAMLAuthOnPrivatePage() ? 'saml': 'form');
        $form->setData('forceSAMLAuthOnLoginPage', $config->mustForceSAMLAuthOnLoginPage() ? 'saml': 'form');
        $form->setData('localLogoutOnly', $config->isLocalLogoutOnly() ?'local':'saml');

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
        $config = new \Jelix\Saml\Configuration(false);
        $this->setupForm($form, $config);

        $tpl = new jTpl();
        $tpl->assign('attrform', $form);

        $loginAction = $config->getLoginAction();
        if ($loginAction) {
            $url = jUrl::getFull($loginAction, ['loginform'=>1]);
        }
        else {
            $url = '';
        }

        $tpl->assign('loginFormUrl', $url);
        $tpl->assign('redirectToSAML', $config->mustRedirectToSAMLAuthOnLoginPage());

        //$rep->addJSLink(jUrl::get('samladmin~config:asset', array('file'=>'sp.js')));
        $rep->body->assign('MAIN', $tpl->fetch('attrmapping'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');
        $rep->addCSSLink(jUrl::get('samladmin~config:asset', array('file'=>'admin.css')));
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
        $config = new \Jelix\Saml\Configuration(false);
        $listOfField = $this->setupForm($form, $config);

        $form->initFromRequest();
        if (!$form->check()) {
            $rep->action = 'samladmin~attrmapping:edit';
            return $rep;
        }

        $config = new \Jelix\Saml\ConfigurationModifier();
        $config->setSAMLAttributeForLogin($form->getData('login'));
        $config->setAutomaticAccountCreation($form->getData('automaticAccountCreation'));
        $config->setAllowSAMLAccountToUseLocalPassword($form->getData('allowSAMLAccountToUseLocalPassword'));
        $config->setForceSAMLAuthOnPrivatePage($form->getData('forceSAMLAuthOnPrivatePage') == 'saml');
        $config->setForceSAMLAuthOnLoginPage($form->getData('forceSAMLAuthOnLoginPage') == 'saml');
        $config->setLocalLogoutOnly($form->getData('localLogoutOnly') == 'local');

        /** @var jFormsControlGroup $groupCtrl */
        $mapping = array();
        foreach($listOfField as $ctrlRef) {
            $mapping[$ctrlRef] = $form->getData('attr_'.$ctrlRef);
        }

        $config->setAttributesMapping($mapping);

        $userGroupsSetting = array('enabled' => $form->getData('groupsfromsaml'));
        $userGroupsSetting['attribute'] = '';
        $userGroupsSetting['separator'] = '';
        $userGroupsSetting['prefix'] = '';
        $userGroupsSetting['drop_prefix'] = false;
        if ($userGroupsSetting['enabled']) {
            $userGroupsSetting['attribute'] = $form->getData('groupsfromsamlattr');
            $userGroupsSetting['prefix'] = $form->getData('groupsfromsamlprefix');
            $userGroupsSetting['drop_prefix'] = $form->getData('groupsfromsamldropprefix');
        }
        $config->setUserGroupsSetting($userGroupsSetting);

        $config->save();
        jForms::destroy('attrmapping');
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }
}
