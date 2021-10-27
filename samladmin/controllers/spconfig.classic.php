<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class spconfigCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );

    public function initform()
    {
        $config = new \Jelix\Saml\Configuration(jApp::config(), false);
        $form = jForms::create('spconfig');

        $org = $config->getOrganization();
        $form->setData('organizationName', $org['name']);
        $form->setData('organizationDisplayName', $org['displayname']);
        $form->setData('organizationUrl', $org['url']);

        $techContact = $config->getTechnicalContact();
        $form->setData('technicalContactPersonName', $techContact['givenName']);
        $form->setData('technicalContactPersonEmail', $techContact['emailAddress']);

        $supportContact = $config->getSupportContact();
        $form->setData('supportContactPersonName', $supportContact['givenName']);
        $form->setData('supportContactPersonEmail', $supportContact['emailAddress']);

        $form->setData('tlsPrivateKey', $config->getSpPrivateKey());
        $form->setData('tlsCertificate', $config->getSpCertificate());
        $rep = $this->getResponse('redirect');
        $rep->action = 'samladmin~spconfig:edit';
        return $rep;
    }


    public function edit()
    {
        $rep = $this->getResponse('html');
        $form = jForms::get('spconfig');
        if (!$form) {
            $rep = $this->getResponse('redirect');
            $rep->action = 'samladmin~config:index';
            return $rep;
        }

        $tpl = new jTpl();
        $tpl->assign('spform', $form);
        $rep->body->assign('MAIN', $tpl->fetch('spconfig'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');
        return $rep;
    }


    function spsave()
    {
        $rep = $this->getResponse('redirect');

        $form = jForms::get('spconfig');
        if (!$form) {
            jMessage::add('missing form', 'error');
            $rep->action = 'samladmin~config:index';
            return $rep;
        }
        $form->initFromRequest();
        if (!$form->check()) {
            $rep->action = 'samladmin~spconfig:edit';
            return $rep;
        }

        $config = new \Jelix\Saml\ConfigurationModifier();
        $config->setOrganization(
            $form->getData('organizationName'),
            $form->getData('organizationDisplayName'),
            $form->getData('organizationUrl')
        );

        $config->setSupportContact(
            $form->getData('supportContactPersonName'),
            $form->getData('supportContactPersonEmail')
        );

        $config->setTechnicalContact(
            $form->getData('technicalContactPersonName'),
            $form->getData('technicalContactPersonEmail')
        );

        $config->setPrivateKey($form->getData('tlsPrivateKey'));
        $config->setCertificate($form->getData('tlsCertificate'));

        $config->save();
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }
}
