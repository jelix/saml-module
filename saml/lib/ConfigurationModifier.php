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

    public function __construct($config = null)
    {
        parent::__construct($config, false);
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

    public function setOrganization($name, $displayName, $url)
    {
        $this->settings['organization']['en-US'] =
            array('name' => $name, 'displayname' => $displayName, 'url' => $url);
    }

    public function save()
    {
        $appConfig = \jApp::config();
        $liveConfig = new \jIniFileModifier(\jApp::configPath('liveconfig.ini.php'));

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
        if ($spPK != '') {
            file_put_contents($spPrivateKeyFile, $spPK);
        }

        $spX509certFile = \jApp::configPath('saml/certs/sp.crt');
        $spCert = $this->getSpCertificate();
        if ($spPK != '') {
            file_put_contents($spX509certFile, $spCert);
        }

        $liveConfig->save();
    }
}
