<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2021 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Constants;

use jLocale;


class Configuration {

    /**
     * @var array setting for the \OneLogin\Saml2 classes
     */
    protected $settings = array();

    /**
     * @var string the SAML attribute that contains the user login
     */
    protected $loginAttribute = '';

    /**
     * @var array mapping between dao record properties (keys) and saml attributes (values)
     */
    protected $attributesMapping = array();

    /**
     * @var array list of dao properties that can be used for mapping
     */
    protected $daoPropertiesForMapping = array();

    protected $idpCertError = '';


    protected $idpLabel = '';

    /**
     * Configuration constructor.
     * @param object $iniConfig typically jApp::config()
     * @throws \jException
     */
    public function __construct($iniConfig = null, $checkConfig = true)
    {
        if (!$iniConfig) {
            $iniConfig = \jApp::config();
        }

        $this->fixConfigValues($iniConfig);
        $spConfig = $iniConfig->{'saml:sp'};

        $this->settings['strict'] = !$spConfig['saml_debug'];
        $this->settings['debug'] = $spConfig['saml_debug'];
        $this->settings['baseurl'] = str_replace('/acs', '/', \jUrl::getFull('saml~endpoint:acs'));

        if (isset($spConfig['daoPropertiesForMapping']) && $spConfig['daoPropertiesForMapping'] != '') {
            $this->daoPropertiesForMapping = preg_split('/ *, */', $spConfig['daoPropertiesForMapping']);
        }

        $this->settings['sp'] = $this->readSpConfig($iniConfig);
        $this->settings['idp'] = $this->readIdPConfig($iniConfig);
        $this->settings['security'] = $iniConfig->{'saml:security'};
        $this->settings['contactPerson'] = array();

        if (isset($spConfig['technicalContactPerson']) &&
            isset($spConfig['technicalContactPerson']['givenName']) &&
            $spConfig['technicalContactPerson']['givenName'] != '' &&
            isset($spConfig['technicalContactPerson']['emailAddress']) &&
            $spConfig['technicalContactPerson']['emailAddress'] != ''
        ) {
            $this->settings['contactPerson']['technical'] = $spConfig['technicalContactPerson'];
        }

        if (isset($spConfig['supportContactPerson']) &&
            isset($spConfig['supportContactPerson']['givenName']) &&
            $spConfig['supportContactPerson']['givenName'] != '' &&
            isset($spConfig['supportContactPerson']['emailAddress']) &&
            $spConfig['supportContactPerson']['emailAddress'] != ''
        ) {
            $this->settings['contactPerson']['support'] = $spConfig['supportContactPerson'];
        }

        if (isset($spConfig['organization'])) {
            $org = array();
            if (isset($spConfig['organization']['name']) &&
                $spConfig['organization']['name'] != ''
            ) {
                $org['name'] = $spConfig['organization']['name'];
            }

            if (isset($spConfig['organization']['displayname']) &&
                $spConfig['organization']['displayname'] != ''
            ) {
                $org['displayname'] = $spConfig['organization']['displayname'];
            }

            if (isset($spConfig['organization']['url']) &&
                $spConfig['organization']['url'] != ''
            ) {
                $org['url'] = $spConfig['organization']['url'];
            }
            if (count($org)) {
                $this->settings['organization'] = array( 'en-US' => $org);
            }
        }

        $this->settings['compress'] = array(
            'requests' => $spConfig['compressRequests'],
            'responses' => $spConfig['compressResponses']
        );

        $attrConfig = $iniConfig->{'saml:attributes-mapping'};
        if (isset($attrConfig['__login']) && $attrConfig['__login'] != '') {
            $this->loginAttribute = $attrConfig['__login'];
        }
        unset($attrConfig['__login']);
        $this->attributesMapping = $attrConfig;

        if ($checkConfig) {
            $this->checkSpConfig();
            $this->checkIdpConfig();
            $this->checkAttrConfig();
        }
    }

    protected function readSpConfig($iniConfig) {
        $spConfig = $iniConfig->{'saml:sp'};

        // Service Provider Data that we are deploying
        $serviceProvider =array(
            // Identifier of the SP entity
            'entityId' => ($spConfig['entityId'] ?? ''),
            // Specifies info about where and how the <AuthnResponse> message MUST be
            // returned to the requester, in this case our SP.
            'assertionConsumerService' => array(
                // URL Location where the <Response> from the IdP will be returned
                'url' => \jUrl::getFull('saml~endpoint:acs'),
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-Redirect binding only
                'binding' => Constants::BINDING_HTTP_POST,
            ),

            // Specifies info about where and how the <Logout Response> message MUST be
            // returned to the requester, in this case our SP.
            'singleLogoutService' => array(
                // URL Location where the <Response> from the IdP will be returned
                'url' => \jUrl::getFull('saml~endpoint:sls'),
                // SAML protocol binding to be used when returning the <Response>
                // message.  Onelogin Toolkit supports for this endpoint the
                // HTTP-Redirect binding only
                'binding' => Constants::BINDING_HTTP_REDIRECT,
            ),
            // Specifies constraints on the name identifier to be used to
            // represent the requested subject.
            // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
            'NameIDFormat' => Constants::NAMEID_UNSPECIFIED,

            'x509cert' => '',
            'privateKey' => '',
        );

        $spX509certFile = \jApp::configPath('saml/certs/sp.crt');
        $spPrivateKeyFile  = \jApp::configPath('saml/certs/sp.key');

        if (file_exists($spX509certFile)) {
            $serviceProvider['x509cert'] = file_get_contents($spX509certFile);
        }

        if (file_exists($spPrivateKeyFile)) {
            $serviceProvider['privateKey'] = file_get_contents($spPrivateKeyFile);
        }

        $spX509certNewFile = \jApp::configPath('saml/certs/sp_new.crt');
        if (file_exists($spX509certNewFile)) {
            $serviceProvider['x509certNew'] = file_get_contents($spX509certNewFile);
        }

        // ---------- requested attributes
        if (isset($spConfig['attrcs_service_name']) &&
            $spConfig['attrcs_service_name'] != '' &&
            isset($iniConfig->{'saml:sp:requestedAttributes'}) &&
            count($iniConfig->{'saml:sp:requestedAttributes'})
        ) {
            $requestedAttributes = array();
            foreach($iniConfig->{'saml:sp:requestedAttributes'} as $attrname =>$properties) {
                $attribute = array( "name" => $attrname);
                foreach(array('isRequired', 'nameFormat', 'friendlyName', 'attributeValue') as $property) {
                    if (isset($properties[$property])) {
                        $attribute[$property] = $properties[$property];
                    }

                }
                $requestedAttributes[] = $attribute;
            }

            $serviceProvider ["attributeConsumingService"] = array(
                "serviceName" => $spConfig['attrcs_service_name'],
                "serviceDescription" => $spConfig['attrcs_service_description'],
                "requestedAttributes" => $requestedAttributes
            );
        }
        return $serviceProvider;
    }

    protected function readIdPConfig($iniConfig) {
        $idpConfig = $iniConfig->{'saml:idp'};

        $this->idpLabel  = $idpConfig['label'];
        $certsSigning = array();
        if ($idpConfig['certs_signing_files'] == '') {
            $idpX509certFile = \jApp::configPath('saml/certs/idp.crt');

            if (file_exists($idpX509certFile)) {
                $idpX509cert = file_get_contents($idpX509certFile);
            }
            else {
                $idpX509cert = '';
                $this->idpCertError = jLocale::get('saml~auth.authentication.error.saml.missing.idp.cert', array('idp.crt'));
            }
        }
        else {
            $idpX509cert = '';
            $list = preg_split('/ *, */', $idpConfig['certs_signing_files']);

            foreach( $list as $file) {
                $path = \jApp::configPath('saml/certs/'.$file);
                if (file_exists($path)) {
                    $certsSigning[] = file_get_contents($path);
                }
                else {
                    $certsSigning = array();
                    $this->idpCertError = jLocale::get('saml~auth.authentication.error.saml.missing.idp.key', array($path));
                    break;
                }
            }
            $list = preg_split('/ *, */', $idpConfig['certs_encryption_files']);
            $certsEncryption = array();
            foreach( $list as $file) {
                $path = \jApp::configPath('saml/certs/'.$file);
                if (file_exists($path)) {
                    $certsEncryption[] = file_get_contents($path);
                }
                else {
                    $certsEncryption = array();
                    $this->idpCertError = jLocale::get('saml~auth.authentication.error.saml.missing.idp.cert', array($path));
                    break;
                }
            }
        }

        $bindings = array(
            'http-post' => Constants::BINDING_HTTP_POST,
            'http-redirect' => Constants::BINDING_HTTP_REDIRECT,
            'http-artifact' => Constants::BINDING_HTTP_ARTIFACT,
            'soap' => Constants::BINDING_SOAP,
            'deflate' => Constants::BINDING_DEFLATE,
        );
        $singleSignOnServiceBinding = $bindings[$idpConfig['singleSignOnServiceBinding']] ?? Constants::BINDING_HTTP_REDIRECT;
        $singleLogoutServiceBinding = $bindings[$idpConfig['singleLogoutServiceBinding']] ?? Constants::BINDING_HTTP_REDIRECT;

        // Identity Provider Data that we want connect with our SP
        $identityProvider = array(
            'entityId' => $idpConfig['entityId'],
            'singleSignOnService' => array(
                'url' => $idpConfig['singleSignOnServiceUrl'],
                'binding' => $singleSignOnServiceBinding,
            ),
            'singleLogoutService' => array(
                'url' => $idpConfig['singleLogoutServiceUrl'],
                'responseUrl' => $idpConfig['singleLogoutServiceResponseUrl'],
                'binding' => $singleLogoutServiceBinding,
            ),
            // Public x509 certificate of the IdP
            'x509cert' => $idpX509cert,
        );

        if (count($certsSigning)) {
            $identityProvider['x509certMulti'] =  array(
                'signing' => $certsSigning,
                'encryption' => $certsEncryption
            );
        }
        return $identityProvider;
    }


    public function checkAttrConfig()
    {
        if ($this->loginAttribute == '' || count($this->attributesMapping) == 0) {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.config.attributes.missing'), 1);
        }
    }


    public function checkSpConfig()
    {

        if ($this->settings['sp']['entityId'] == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.sp.entityId'),2);
        }

        $org = $this->getOrganization();
        if ($org['name'] == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.sp.organization'),2);
        }

        if ($this->settings['sp']['x509cert'] == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.sp.cert'),2);
        }

        if ($this->settings['sp']['privateKey'] == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.sp.key'), 2);
        }
    }

    public function checkIdpConfig()
    {
        if ($this->idpCertError != '') {
            throw new \Exception($this->idpCertError, 10);
        }

        if ($this->getIdpEntityId() == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.idp.identityId'),2);
        }

        if ($this->settings['idp']['singleSignOnService']['binding'] == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.bad.parameter', array('singleSignOnServiceBinding')), 11);
        }
        if ($this->settings['idp']['singleLogoutService']['binding'] == '') {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.bad.parameter', array('singleLogoutServiceBinding')), 11);
        }
    }

    /**
     * All SAML settings as a Settings object.
     *
     * @return Settings
     * @throws \OneLogin\Saml2\Error
     */
    function getSettings() {
        return new Settings($this->settings);
    }

    /**
     * All SAML settings as an array
     * @return array
     */
    function getSettingsArray() {
        return $this->settings;
    }

    /**
     * to get the application entity id
     * @return string the entity Id
     */
    function getSpEntityId()
    {
        return $this->settings['sp']['entityId'];
    }

    /**
     * to get the TLS certificate of the service provider
     * @return string the content of the certificate (PEM format)
     */
    function getSpCertificate()
    {
        return $this->settings['sp']['x509cert'];
    }

    /**
     * to get the private key to sign the certificate of the service provider
     * @return string the content of the private key
     */
    function getSpPrivateKey()
    {
        return $this->settings['sp']['privateKey'];
    }

    /**
     * Name and email of the contact person for the support
     * @return array  attributes: 'givenName' and 'emailAddress'
     */
    function getSupportContact()
    {
        return ($this->settings['contactPerson']['support'] ??
            array('givenName'=>'', 'emailAddress'=>''));
    }

    /**
     * Name and email of the contact person for the technic
     * @return array  attributes: 'givenName' and 'emailAddress'
     */
    function getTechnicalContact()
    {
        return ($this->settings['contactPerson']['technical'] ??
            array('givenName'=>'', 'emailAddress'=>''));
    }

    /**
     * to get organisation properties
     * @return array attributes: 'name', 'displayname', 'url'
     */
    function getOrganization()
    {
        $org =
            array_merge(
                array('name' => '', 'displayname' => '', 'url' => ''),
                ($this->settings['organization']['en-US'] ?? array())
            );
        return $org;
    }

    function getIdpURL()
    {
        return array(
            'singleSignOnService' => $this->settings['idp']['singleSignOnService']['url'],
            'singleLogoutService' => $this->settings['idp']['singleLogoutService']['url'],
            'singleLogoutServiceResponse' => $this->settings['idp']['singleLogoutService']['responseUrl']
        );
    }


    function getIdpLabel()
    {
        return $this->idpLabel;
    }

    function getIdpEntityId()
    {
        return $this->settings['idp']['entityId'];
    }

    function getIdpSigningCertificate()
    {
        if (isset($this->settings['idp']['x509certMulti']['signing'])) {
            $certs = $this->settings['idp']['x509certMulti']['signing'];
            if (count($certs)) {
                return $certs[0];
            }
        }
        return $this->settings['idp']['x509cert'];
    }

    function getIdpEncryptionCertificate()
    {
        if (isset($this->settings['idp']['x509certMulti']['encryption'])) {
            $certs = $this->settings['idp']['x509certMulti']['encryption'];
            if (count($certs)) {
                return $certs[0];
            }
        }
        return $this->settings['idp']['x509cert'];
    }


    /**
     * Gives the SAML attribute that contains the user login
     *
     * @return string
     */
    function getSAMLAttributeForLogin()
    {
        return $this->loginAttribute;
    }

    /**
     * @return array keys are dao attributes, values are SAML attributes
     */
    function getAttributesMapping()
    {
        return $this->attributesMapping;
    }


    /**
     * list of dao properties that could be mapped
     *
     * @return array dao properties names
     */
    function getAuthorizedDaoPropertiesForMapping()
    {
        return $this->daoPropertiesForMapping;
    }

    protected function fixConfigValues($iniConfig) {
        $boolVal = array('saml_debug', 'compressRequests', 'compressResponses');
        $this->fixBool($iniConfig, 'saml:sp', $boolVal);

        $boolVal = array('nameIdEncrypted', 'authnRequestsSigned', 'logoutRequestSigned',
            'logoutResponseSigned', 'signMetadata', 'wantMessagesSigned',
            'wantAssertionsEncrypted', 'wantAssertionsSigned', 'wantNameId',
            'wantNameIdEncrypted', 'requestedAuthnContext', 'wantXMLValidation',
            'relaxDestinationValidation', 'destinationStrictlyMatches',
            'rejectUnsolicitedResponsesWithInResponseTo', 'lowercaseUrlencoding'
        );
        $this->fixBool($iniConfig, 'saml:security', $boolVal);
    }

    protected function fixBool($iniConfig, $section, $values) {
        foreach ($values as $name) {
            $val = $iniConfig->{$section}[$name];
            if (is_bool($val)) {
                continue;
            }
            if ($val) {
                $iniConfig->{$section}[$name] = true;
            }
            else {
                $iniConfig->{$section}[$name] = false;
            }
        }
    }
}
