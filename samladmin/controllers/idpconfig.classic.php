<?php

use OneLogin\Saml2\Constants;

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
        $config = new \Jelix\Saml\Configuration(false);
        $form = jForms::create('idpconfig');

        $form->setData('serviceLabel', $config->getIdpLabel());
        $form->setData('entityId', $config->getIdpEntityId());

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
        $rep->addJSLink(jUrl::get('samladmin~config:asset', array('file'=>'idp.js')));
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
        $config->setIdpEntityId($form->getData('entityId'));
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

    function loadMetadata()
    {
        /** @var jResponseJson $rep */
        $rep = $this->getResponse('json');
        $url = $this->param('metadata_url');
        if (!$url) {
            $rep->data = array('error'=>'url is missing');
            $rep->setHttpStatus(400, 'Bad request');
            return $rep;
        }

        try {
            $parser   = new \OneLogin\Saml2\IdPMetadataParser();
            $metadata = $parser->parseRemoteXML($url, null, null, Constants::BINDING_HTTP_REDIRECT, Constants::BINDING_HTTP_REDIRECT);
        }
        catch(Exception $e) {
            $rep->data = array(
                'error'=>jLocale::get('samladmin~admin.spconfig.form.error.metadata'),
                'parserError'=> $e->getMessage()
            );
            $rep->setHttpStatus(500, 'Internal server error');
            return $rep;
        }

        $metadata = $metadata['idp'];
        $data = array(
            'entityId' => $metadata['entityId'],
            'singleSignOnServiceUrl' => $metadata['singleSignOnService']['url']??'',
            'singleLogoutServiceUrl' => $metadata['singleLogoutService']['url']??'',
            'singleLogoutServiceResponseUrl' => $metadata['singleLogoutService']['responseUrl']??'',
            'signingCertificate' => '',
            'encryptionCertificate' => ''
        );

        if (isset($metadata['x509certMulti'])) {
            $data['signingCertificate'] = $metadata['x509certMulti']['signing'][0]??'';
            $data['encryptionCertificate'] = $metadata['x509certMulti']['encryption'][0]??'';
        }
        else if (isset($metadata['x509cert'])) {
            $data['signingCertificate'] = $metadata['x509cert'];
            $data['encryptionCertificate'] = $metadata['x509cert'];
        }

        $data['signingCertificate'] = trim($data['signingCertificate']);
        $data['encryptionCertificate'] = trim($data['encryptionCertificate']);

        if ($data['signingCertificate'] && strpos($data['signingCertificate'], "-----BEGIN CERTIFICATE-----") === false) {
            $data['signingCertificate'] = "-----BEGIN CERTIFICATE-----\n".$data['signingCertificate']."\n-----END CERTIFICATE-----\n";
        }

        if ($data['encryptionCertificate'] && strpos($data['encryptionCertificate'], "-----BEGIN CERTIFICATE-----") === false) {
            $data['encryptionCertificate'] = "-----BEGIN CERTIFICATE-----\n".$data['encryptionCertificate']."\n-----END CERTIFICATE-----\n";
        }

        $rep->data = $data;
        return $rep;
    }

}
