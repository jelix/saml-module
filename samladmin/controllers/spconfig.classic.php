<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use phpseclib3\File\X509;
use phpseclib3\Crypt\RSA;

class spconfigCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );

    public function initform()
    {
        $config = new \Jelix\Saml\Configuration(false);
        $form = jForms::create('spconfig');

        $form->setData('entityId', $config->getSpEntityId());

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

        $certForm = jForms::create('cert');

        $tpl = new jTpl();
        $tpl->assign('spform', $form);
        $tpl->assign('certForm', $certForm);
        $rep->addJSLink(jUrl::get('samladmin~config:asset', array('file'=>'sp.js')));
        $rep->body->assign('MAIN', $tpl->fetch('spconfig'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');
        return $rep;
    }


    function save()
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

        $config->setSpEntityId($form->getData('entityId'));

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
        jForms::destroy('spconfig');
        jMessage::add(jLocale::get('samladmin~admin.spconfig.form.save.ok', 'notice'));
        $rep->action = 'samladmin~config:index';
        return $rep;
    }

    function generateKey()
    {
        /** @var jResponseJson $rep */
        $rep = $this->getResponse('json');
        $keyLength = $this->intParam('keylength');
        if (!$keyLength) {
            $rep->data = array('error'=>'keyLength is missing');
            $rep->setHttpStatus(400, 'Bad request');
            return $rep;
        }

        if ($keyLength != 2048 && $keyLength != 3072 && $keyLength != 4096){
            $rep->data = array('error'=>'bad key length');
            $rep->setHttpStatus(400, 'Bad request');
            return $rep;
        }

        $pk = RSA::createKey($keyLength);
        $rep->data = array('privateKey' => (string)$pk);
        return $rep;
    }


    function generateCert()
    {
        $rep = $this->getResponse('json');

        $privKey = \phpseclib3\Crypt\PublicKeyLoader::loadPrivateKey($this->param('privKey'));
        $privKey = $privKey->withPadding(RSA::ENCRYPTION_PKCS1 | RSA::SIGNATURE_PKCS1);
        $pubKey = $privKey->getPublicKey();

        $subject = new X509;

        $dn = array();
        $fields = array(
            'certCountryName' => 'C',
            'certStateOrProvinceName' => 'ST',
            'certLocalityName' => 'L',
            'certOrganizationName' => 'O',
            'certOrganizationalUnitName' => 'OU',
        );
        foreach($fields as $field => $code)
        {
            $certValue = $this->param($field);
            if ($certValue) {
                $dn[] = $code.'='.$certValue;
            }
        }

        $subject->setDN(implode(', ', $dn));
        $subject->setPublicKey($pubKey);
        $domain = $this->param('certCommonName');
        str_replace(array('http://', 'https://'), '', $domain);
        $subject->setDomain($domain);

        $issuer = new X509;
        $issuer->setPrivateKey($privKey);
        $issuer->setDN($subject->getDN());

        $x509 = new X509;

        $dateStart = new DateTime();
        $x509->setStartDate($dateStart);
        $dateEnd = new DateTime();
        $dateEnd->add(new DateInterval("P".$this->param('certDaysValidity')."D"));
        $x509->setEndDate($dateEnd);

        $result = $x509->sign($issuer, $subject);

        $rep->data= array('certificate'=> $x509->saveX509($result));
        return $rep;
    }

}
