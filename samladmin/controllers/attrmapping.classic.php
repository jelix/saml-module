<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021-2026 3liz
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

        $daoProperties = $config->getAuthorizedDaoPropertiesForMapping();

        if (!in_array('email', $daoProperties)) {
            array_unshift($daoProperties, 'email');
        }

        if (!in_array('login', $daoProperties)) {
            array_unshift($daoProperties, 'login');
        }

        $userFormSelector = jAuth::getDriverParam('form');
        $authForm = jForms::create($userFormSelector);

        foreach ($daoProperties as $daoProperty) {

            $ctrl = $authForm->getControl($daoProperty);
            $attrCtrl = new jFormsControlInput('attr_'.$daoProperty);
            $label = '';

            if (!$ctrl) {
                $attrCtrl->required = ($daoProperty == 'login' || $daoProperty == 'email');
            }
            else {
                $label = $ctrl->label;
                $attrCtrl->required = ($daoProperty == 'login' || $daoProperty == 'email')? true : $ctrl->required;
            }

            if ($daoProperty == 'login') {
                $label = jLocale::get('samladmin~admin.attrmapping.form.login.label');
            }
            else if ($label == '') {
                $label = $daoProperty;
            }
            $attrCtrl->label = $label;
            $attrCtrl->defaultValue = $defaultValues[$daoProperty] ?? '';

            $groupCtrl->addChildControl($attrCtrl);
        }

        $form->addControl($groupCtrl);
        jForms::destroy($userFormSelector);

        $conf = jApp::coord()->getPlugin('auth')->config;
        if ($conf['after_login'] == 'master_admin~default:index' || $conf['after_login'] == 'adminui~default:index') {
            $alLabel = jLocale::get('samladmin~admin.attrmapping.form.redirectionAfterLogin.dashboard');
        }
        else {
            $alLabel = jLocale::get('samladmin~admin.attrmapping.form.redirectionAfterLogin.defaultpage', [ jUrl::get($conf['after_login'])]);
        }
        $ds = new jFormsStaticDatasource();
        $ds->data = array(
            '' => $alLabel,
            'homepage' => jLocale::get('samladmin~admin.attrmapping.form.redirectionAfterLogin.homepage')
        );
        $form->getControl('redirectionAfterLogin')->datasource = $ds;

        if ($config->mustRedirectToSAMLAuthOnLoginPage()) {
            $ctrl = $form->getControl('forceSAMLAuthOnLoginPage');
            $ctrl->label = jLocale::get('samladmin~admin.attrmapping.form.forceSAMLAuthOnLoginPage.redirectlabel');
        }

        return $daoProperties;
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

        $form->setData('automaticAccountCreation', $config->isAutomaticAccountCreation());
        $form->setData('allowSAMLAccountToUseLocalPassword', $config->isAllowingSAMLAccountToUseLocalPassword());
        $form->setData('forceSAMLAuthOnPrivatePage', $config->mustForceSAMLAuthOnPrivatePage() ? 'saml': 'form');
        $form->setData('forceSAMLAuthOnLoginPage', $config->mustForceSAMLAuthOnLoginPage() ? 'saml': 'form');
        $form->setData('localLogoutOnly', $config->isLocalLogoutOnly() ?'local':'saml');

        $conf = jApp::coord()->getPlugin('auth')->config;
        if ($conf['after_login'] == '') {
            $form->setData('redirectionAfterLogin', 'homepage');
            $form->deactivate('redirectionAfterLogin');
        }
        else {
            $defaultUrl = jUrl::get($conf['after_login']);
            $bp = jApp::urlBasePath();
            if ($defaultUrl == $bp || $defaultUrl == $bp.'index.php') {
                $form->setData('redirectionAfterLogin', 'homepage');
                $form->deactivate('redirectionAfterLogin');
            }
            else {
                $form->deactivate('redirectionAfterLogin', false);
                $form->setData('redirectionAfterLogin', $config->getRedirectionAfterLogin());
            }
        }

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
        $config->setAutomaticAccountCreation($form->getData('automaticAccountCreation'));
        $config->setAllowSAMLAccountToUseLocalPassword($form->getData('allowSAMLAccountToUseLocalPassword'));
        $config->setForceSAMLAuthOnPrivatePage($form->getData('forceSAMLAuthOnPrivatePage') == 'saml');
        $config->setForceSAMLAuthOnLoginPage($form->getData('forceSAMLAuthOnLoginPage') == 'saml');
        $config->setLocalLogoutOnly($form->getData('localLogoutOnly') == 'local');

        $ctrl = $form->getControl('redirectionAfterLogin');
        if ($ctrl->isActivated()) {
            $config->setRedirectionAfterLogin($form->getData('redirectionAfterLogin'));
        }

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
