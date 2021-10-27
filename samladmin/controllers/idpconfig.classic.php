<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
class idpconfigCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );

    public function initform()
    {
        $config = new \Jelix\Saml\Configuration(jApp::config(), false);
        $form = jForms::create('idpconfig');

        $form->setData('serviceLabel', $config->getIdpLabel());

        $urls = $config->getIdpURL();
        $form->setData('singleSignOnServiceUrl', $urls['singleSignOnService']);
        $form->setData('singleLogoutServiceUrl', $urls['singleLogoutService']);
        $form->setData('singleLogoutServiceResponseUrl', $urls['singleLogoutServiceResponse']);

        $form->setData('signingCertificate', $config->getIdpSigningCertificate());
        $form->setData('encryptionCertificate', $config->getIdpEncryptionCertificate());

        $rep = $this->getResponse('redirect');
        $rep->action = 'samladmin~idpconfig:edit';
        return $rep;
    }


    public function edit()
    {
        $rep = $this->getResponse('html');
        $form = jForms::get('idpconfig');
        if (!$form) {
            $rep = $this->getResponse('redirect');
            $rep->action = 'samladmin~config:index';
            return $rep;
        }

        $tpl = new jTpl();
        $tpl->assign('idpform', $form);
        $rep->body->assign('MAIN', $tpl->fetch('idpconfig'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');
        return $rep;
    }


    function save()
    {
        $rep = $this->getResponse('redirect');

        $form = jForms::get('idpconfig');
        if (!$form) {
            jMessage::add('missing form', 'error');
            $rep->action = 'samladmin~config:index';
            return $rep;
        }
        $form->initFromRequest();
        if (!$form->check()) {
            $rep->action = 'samladmin~idpconfig:edit';
            return $rep;
        }

        $config = new \Jelix\Saml\ConfigurationModifier();
        $config->setIdpLabel($form->getData('serviceLabel'));
        $config->setIdpSigningCertificate($form->getData('signingCertificate'));
        $config->setIdpEncryptionCertificate($form->getData('encryptionCertificate'));

        $config->setIdpUrls(
            $form->getData('singleSignOnServiceUrl'),
            $form->getData('singleLogoutServiceUrl'),
            $form->getData('singleLogoutServiceResponseUrl')
        );

        $config->save();
        jForms::destroy('idpconfig');
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }
}
