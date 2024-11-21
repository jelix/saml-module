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

        $daoProperties = $config->getAuthorizedDaoPropertiesForMapping();

        $userFormSelector = jAuth::getDriverParam('form');
        $authForm = jForms::create($userFormSelector);
        $listOfField = array();
        foreach ($authForm->getControls() as $ctrlName => $ctrl) {
            if (! ($ctrl instanceof jFormsControlInput)) {
                continue;
            }
            $listOfField[] = $ctrl->ref;
            if (count($daoProperties) && !in_array($ctrl->ref, $daoProperties)) {
                continue;
            }

            $attrCtrl = new jFormsControlInput('attr_'.$ctrl->ref);
            $attrCtrl->label = $ctrl->label;
            $attrCtrl->defaultValue = $defaultValues[$ctrl->ref] ?? '';
            $attrCtrl->required = $ctrl->required;
            $groupCtrl->addChildControl($attrCtrl);
        }
        $form->addControl($groupCtrl);
        jForms::destroy($userFormSelector);

        $ctrl = $form->getControl('redirectionAfterLogin');
        if ($ctrl->isActivated()) {
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
            $form->setData('groupsfromsamlsep', $userGroupsSetting['separator'] ?? '');
            $form->setData('groupsfromsamlprefix', $userGroupsSetting['prefix'] ?? '');
        }

        $form->setData('login', $config->getSAMLAttributeForLogin());
        $form->setData('automaticAccountCreation', $config->isAutomaticAccountCreation());
        $form->setData('allowSAMLAccountToUseLocalPassword', $config->isAllowingSAMLAccountToUseLocalPassword());
        $form->setData('forceSAMLAuthOnPrivatePage', $config->mustForceSAMLAuthOnPrivatePage());

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
        $config->setForceSAMLAuthOnPrivatePage($form->getData('forceSAMLAuthOnPrivatePage'));
        $ctrl = $form->getControl('redirectionAfterLogin');
        if ($ctrl->isActivated()) {
            $config->setRedirectionAfterLogin($form->getData('redirectionAfterLogin'));
        }

        $daoProperties = $config->getAuthorizedDaoPropertiesForMapping();

        /** @var jFormsControlGroup $groupCtrl */
        $mapping = array();
        foreach($listOfField as $ctrlRef) {
            if (count($daoProperties) == 0  || in_array($ctrlRef, $daoProperties)) {
                $samlAttr = $form->getData('attr_'.$ctrlRef);
            }
            else {
                $samlAttr = '';
            }
            $mapping[$ctrlRef] = $samlAttr;
        }

        $config->setAttributesMapping($mapping);

        $userGroupsSetting = array('enabled' => $form->getData('groupsfromsaml'));
        $userGroupsSetting['attribute'] = '';
        $userGroupsSetting['separator'] = '';
        $userGroupsSetting['prefix'] = '';
        if ($userGroupsSetting['enabled']) {
            $userGroupsSetting['attribute'] = $form->getData('groupsfromsamlattr');
            $userGroupsSetting['separator'] = $form->getData('groupsfromsamlsep');
            $userGroupsSetting['prefix'] = $form->getData('groupsfromsamlprefix');
        }
        $config->setUserGroupsSetting($userGroupsSetting);

        $config->save();
        jForms::destroy('attrmapping');
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }
}
