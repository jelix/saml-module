<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2021 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Constants;

use jLocale;


class ConfigurationModifier extends Configuration
{
    /**
     * @param object $iniConfig typically jApp::config()
     * @throws \jException
     */
    public function __construct($iniConfig = null)
    {
        parent::__construct(false, $iniConfig);
    }

    /**
     * @param bool $onlyNameID
     */
    public function setUseOnlyNameIDAssertionToAuthenticate($onlyNameID)
    {
        $this->useOnlyNameIDAssertionToAuthenticate = !!$onlyNameID;
    }

    /**
     * @param bool $automatic
     */
    public function setAutomaticAccountCreation($automatic)
    {
        $this->automaticAccountCreation = !!$automatic;
    }

    /**
     * @param bool $automatic
     */
    public function setAllowSAMLAccountToUseLocalPassword($allow)
    {
        $this->allowSAMLAccountToUseLocalPassword = !!$allow;
    }

    public function setSpEntityId($entityId)
    {
        $this->settings['sp']['entityId'] = $entityId;
    }

    public function setCertificate($certificate)
    {
        $this->settings['sp']['x509cert'] = $certificate;
    }

    public function setPrivateKey($key)
    {
        $this->settings['sp']['privateKey'] = $key;
    }

    public function setSupportContact($name, $email)
    {
        $this->settings['contactPerson']['support'] =
            array('givenName'=>$name, 'emailAddress'=>$email);
    }

    public function setTechnicalContact($name, $email)
    {
        $this->settings['contactPerson']['technical'] =
            array('givenName'=>$name, 'emailAddress'=>$email);
    }

    /**
     * @param $name
     * @param $displayName
     * @param $url
     * @return bool true if values are ok, false if some values are missing
     */
    public function setOrganization($name, $displayName, $url)
    {
        $this->settings['organization']['en-US'] =
            array('name' => $name, 'displayname' => $displayName, 'url' => $url);

        if ($name === '' && $displayName === '' && $url === '') {
            return true;
        }
        if ($name !== '' && $displayName !== '' && $url !== '') {
            return true;
        }
        return false;
    }

    public function setIdpLabel($label)
    {
        $this->idpLabel = $label;
    }

    public function setIdpEntityId($entityId)
    {
        $this->settings['idp']['entityId'] = $entityId;
    }

    public function setIdpUrls($singleSignOnServiceUrl, $singleLogoutServiceUrl, $singleLogoutServiceResponseUrl)
    {
        $this->settings['idp']['singleSignOnService']['url'] = $singleSignOnServiceUrl;
        $this->settings['idp']['singleLogoutService']['url'] = $singleLogoutServiceUrl;
        $this->settings['idp']['singleLogoutService']['responseUrl'] = $singleLogoutServiceResponseUrl;
    }

    public function setIdpSigningCertificate($certificate)
    {
        $this->settings['idp']['x509certMulti']['signing'] = array($certificate);
    }

    public function setIdpEncryptionCertificate($certificate)
    {
        $this->settings['idp']['x509certMulti']['encryption'] = array($certificate);
    }

    public function setSAMLAttributeForLogin($attr)
    {
        $this->loginAttribute = $attr;
    }


    public function setAttributesMapping(array $attributes)
    {
        $this->attributesMapping = $attributes;
    }


    public function save()
    {
        $appConfig = \jApp::config();
        $liveConfig = new \jIniFileModifier(\jApp::configPath('liveconfig.ini.php'));

        // sp data
        $spEid = $this->getSpEntityId();
        $liveConfig->setValue('entityId', $spEid, 'saml:sp');
        $appConfig->{'saml:sp'}['entityId'] = $spEid;

        $tech = $this->getTechnicalContact();
        $liveConfig->setValue('technicalContactPerson', $tech['givenName'], 'saml:sp', 'givenName');
        $liveConfig->setValue('technicalContactPerson', $tech['emailAddress'], 'saml:sp', 'emailAddress');
        $appConfig->{'saml:sp'}['technicalContactPerson'] = $tech;

        $support = $this->getSupportContact();
        $liveConfig->setValue('supportContactPerson', $support['givenName'], 'saml:sp', 'givenName');
        $liveConfig->setValue('supportContactPerson', $support['emailAddress'], 'saml:sp', 'emailAddress');
        $appConfig->{'saml:sp'}['supportContactPerson'] = $support;

        $org = $this->getOrganization();
        $liveConfig->setValue('organization', $org['name'], 'saml:sp', 'name');
        $liveConfig->setValue('organization', $org['displayname'], 'saml:sp', 'displayname');
        $liveConfig->setValue('organization', $org['url'], 'saml:sp', 'url');
        $appConfig->{'saml:sp'}['organization'] = $org;


        $spPrivateKeyFile  = \jApp::configPath('saml/certs/sp.key');
        $spPK = $this->getSpPrivateKey();
        $this->saveConfigFile($spPrivateKeyFile, $spPK);

        $spX509certFile = \jApp::configPath('saml/certs/sp.crt');
        $spCert = $this->getSpCertificate();
        $this->saveConfigFile($spX509certFile, $spCert);

        // idp data
        $liveConfig->setValue('label', $this->getIdpLabel(), 'saml:idp');
        $appConfig->{'saml:idp'}['label'] = $this->getIdpLabel();

        $idpEid = $this->getIdpEntityId();
        $liveConfig->setValue('entityId', $idpEid, 'saml:idp');
        $appConfig->{'saml:idp'}['entityId'] = $idpEid;

        $urls = $this->getIdpURL();

        $liveConfig->setValue('singleSignOnServiceUrl', $urls['singleSignOnService'], 'saml:idp');
        $appConfig->{'saml:idp'}['singleSignOnServiceUrl'] = $urls['singleSignOnService'];
        $liveConfig->setValue('singleLogoutServiceUrl', $urls['singleLogoutService'], 'saml:idp');
        $appConfig->{'saml:idp'}['singleLogoutServiceUrl'] = $urls['singleLogoutService'];
        $liveConfig->setValue('singleLogoutServiceResponseUrl', $urls['singleLogoutServiceResponse'], 'saml:idp');
        $appConfig->{'saml:idp'}['singleLogoutServiceResponseUrl'] = $urls['singleLogoutServiceResponse'];

        $idpX509certFile = \jApp::configPath('saml/certs/idp.crt');
        if (file_exists($idpX509certFile)) {
            unlink($idpX509certFile);
        }

        $idpSignCertFile = \jApp::configPath('saml/certs/idp_sig.pem');
        $signCert = $this->getIdpSigningCertificate();
        $this->saveConfigFile($idpSignCertFile, $signCert);
        $liveConfig->setValue('certs_signing_files', ($signCert ? 'idp_sig.pem' : ''), 'saml:idp');
        $appConfig->{'saml:idp'}['certs_signing_files'] = $signCert ? 'idp_sig.pem' : '';

        $idpEncryptCertFile = \jApp::configPath('saml/certs/idp_encrypt.pem');
        $cryptCert = $this->getIdpEncryptionCertificate();
        $this->saveConfigFile($idpEncryptCertFile, $cryptCert);
        $liveConfig->setValue('certs_encryption_files', ($cryptCert ? 'idp_encrypt.pem' : ''), 'saml:idp');
        $appConfig->{'saml:idp'}['certs_encryption_files'] = $cryptCert ? 'idp_encrypt.pem' : '';

        $mapping = $this->attributesMapping;
        $mapping['__login'] = $this->loginAttribute;
        $liveConfig->setValues($mapping, 'saml:attributes-mapping');
        $appConfig->{'saml:attributes-mapping'} = $mapping;

        $liveConfig->setValue('useOnlyNameIDAssertionToAuthenticate', $this->useOnlyNameIDAssertionToAuthenticate, 'saml');
        $appConfig->saml['useOnlyNameIDAssertionToAuthenticate'] = $this->useOnlyNameIDAssertionToAuthenticate;

        $liveConfig->setValue('automaticAccountCreation', $this->automaticAccountCreation, 'saml');
        $appConfig->saml['automaticAccountCreation'] = $this->automaticAccountCreation;

        $liveConfig->setValue('allowSAMLAccountToUseLocalPassword', $this->allowSAMLAccountToUseLocalPassword, 'saml');
        $appConfig->saml['allowSAMLAccountToUseLocalPassword'] = $this->allowSAMLAccountToUseLocalPassword;

        $liveConfig->save();
    }


    protected function saveConfigFile($path, $content)
    {
        if ($content == '') {
            if (file_exists($path)) {
                unlink($path);
            }
            return;
        }

        if (file_exists($path)) {
            $originalContent = file_get_contents($path);
            if ($originalContent == $content) {
                // don't modify the file if already exists
                return;
            }
        }

        file_put_contents($path, $content);
    }


}
