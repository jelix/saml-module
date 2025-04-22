<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2021-2024 3Liz
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
     * @param bool $automatic
     */
    public function setAutomaticAccountCreation($automatic)
    {
        $this->automaticAccountCreation = !!$automatic;
    }

    /**
     * @param bool $allow
     */
    public function setAllowSAMLAccountToUseLocalPassword($allow)
    {
        $this->allowSAMLAccountToUseLocalPassword = !!$allow;
    }

    /**
     * @param bool $allow
     */
    public function setForceSAMLAuthOnPrivatePage($allow)
    {
        $this->forceSAMLAuthOnPrivatePage = !!$allow;
    }

    /**
     * @param bool $allow
     */
    public function setForceSAMLAuthOnLoginPage($allow)
    {
        $this->forceSAMLAuthOnLoginPage = !!$allow;
    }

    public function setRedirectionAfterLogin($redir)
    {
        $this->redirectionAfterLogin = $redir;
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

    public function setUserGroupsSetting(array $setting)
    {
        $this->userGroupsSetting = $setting;
    }

    public function save()
    {
        $appConfig = \jApp::config();
        $liveConfig = new \jIniFileModifier($this->configPath('liveconfig.ini.php'));

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


        $spPrivateKeyFile  = $this->configPath('saml/certs/sp.key');
        $spPK = $this->getSpPrivateKey();
        $this->saveConfigFile($spPrivateKeyFile, $spPK);

        $spX509certFile = $this->configPath('saml/certs/sp.crt');
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

        $idpX509certFile = $this->configPath('saml/certs/idp.crt');
        if (file_exists($idpX509certFile)) {
            unlink($idpX509certFile);
        }

        $idpSignCertFile = $this->configPath('saml/certs/idp_sig.pem');
        $signCert = $this->getIdpSigningCertificate();
        $this->saveConfigFile($idpSignCertFile, $signCert);
        $liveConfig->setValue('certs_signing_files', ($signCert ? 'idp_sig.pem' : ''), 'saml:idp');
        $appConfig->{'saml:idp'}['certs_signing_files'] = $signCert ? 'idp_sig.pem' : '';

        $idpEncryptCertFile = $this->configPath('saml/certs/idp_encrypt.pem');
        $cryptCert = $this->getIdpEncryptionCertificate();
        $this->saveConfigFile($idpEncryptCertFile, $cryptCert);
        $liveConfig->setValue('certs_encryption_files', ($cryptCert ? 'idp_encrypt.pem' : ''), 'saml:idp');
        $appConfig->{'saml:idp'}['certs_encryption_files'] = $cryptCert ? 'idp_encrypt.pem' : '';

        $mapping = $this->attributesMapping;
        $mapping['__login'] = $this->loginAttribute;
        $liveConfig->setValues($mapping, 'saml:attributes-mapping');
        $appConfig->{'saml:attributes-mapping'} = $mapping;

        $ugSetting = $this->userGroupsSetting;
        $liveConfig->setValues($ugSetting, 'saml:userGroups-setting');
        $appConfig->{'saml:userGroups-setting'} = $ugSetting;

        $liveConfig->setValue('automaticAccountCreation', $this->automaticAccountCreation, 'saml');
        $appConfig->saml['automaticAccountCreation'] = $this->automaticAccountCreation;

        $liveConfig->setValue('allowSAMLAccountToUseLocalPassword', $this->allowSAMLAccountToUseLocalPassword, 'saml');
        $appConfig->saml['allowSAMLAccountToUseLocalPassword'] = $this->allowSAMLAccountToUseLocalPassword;

        $liveConfig->setValue('forceSAMLAuthOnPrivatePage', $this->forceSAMLAuthOnPrivatePage, 'saml');
        $appConfig->saml['forceSAMLAuthOnPrivatePage'] = $this->forceSAMLAuthOnPrivatePage;

        $liveConfig->setValue('forceSAMLAuthOnLoginPage', $this->forceSAMLAuthOnLoginPage, 'saml');
        $appConfig->saml['forceSAMLAuthOnLoginPage'] = $this->forceSAMLAuthOnLoginPage;

        $liveConfig->setValue('redirectionAfterLogin', $this->redirectionAfterLogin, 'saml');
        $appConfig->saml['redirectionAfterLogin'] = $this->redirectionAfterLogin;

        $liveConfig->save();

        // touch the file into the futur, so there is a chance that the cache file is older than liveconfig,
        // and so it is refreshed.
        touch($this->configPath('liveconfig.ini.php'), time()+2);
        clearstatcache();
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
