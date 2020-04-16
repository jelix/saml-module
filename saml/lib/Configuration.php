<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;

use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Constants;

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
     * Configuration constructor.
     * @param \jRequest $request
     * @param object $iniConfig typically jApp::config()
     * @throws \jException
     */
    public function __construct(\jRequest $request, $iniConfig = null)
    {
        if (!$iniConfig) {
            $iniConfig = \jApp::config();
        }

        $this->fixConfigValues($iniConfig);
        $spConfig = $iniConfig->{'saml:sp'};

        $this->settings['strict'] = !$spConfig['saml_debug'];
        $this->settings['debug'] = $spConfig['saml_debug'];
        $this->settings['baseurl'] = str_replace('/acs', '/', \jUrl::getFull('saml~endpoint:acs'));

        $this->settings['sp'] = $this->getSpConfig($iniConfig);
        $this->settings['idp'] = $this->getIdPConfig($iniConfig);
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
        if (!isset($attrConfig['__login']) || $attrConfig['__login'] == '') {
            throw new \Exception('__login is missing into the attributes mapping configuration');
        }
        $this->loginAttribute = $attrConfig['__login'];
        unset($attrConfig['__login']);
        $this->attributesMapping = $attrConfig;
    }

    protected function getSpConfig($iniConfig) {
        $spConfig = $iniConfig->{'saml:sp'};

        $spX509certFile = \jApp::configPath('saml/certs/sp.crt');
        $spPrivateKey  = \jApp::configPath('saml/certs/sp.key');

        if (!file_exists($spX509certFile)) {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.sp.cert'));
        }

        if (!file_exists($spPrivateKey)) {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.sp.key'));
        }

        // Service Provider Data that we are deploying
        $serviceProvider =array(
            // Identifier of the SP entity  (must be a URI)
            'entityId' => \jUrl::getFull('saml~endpoint:metadata'),
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

            'x509cert' => file_get_contents($spX509certFile),
            'privateKey' => file_get_contents($spPrivateKey),
        );

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

    protected function getIdPConfig($iniConfig) {
        $idpConfig = $iniConfig->{'saml:idp'};

        if ($idpConfig['certs_signing_files'] == '') {
            $idpX509certFile = \jApp::configPath('saml/certs/idp.crt');

            if (!file_exists($idpX509certFile) && $idpConfig['certs_signing_files'] == '') {
                throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.idp.cert', array('idp.crt')));
            }
            $idpX509cert = file_get_contents($idpX509certFile);
        }
        else {
            $idpX509cert = '';
            $list = preg_split('/ *, */', $idpConfig['certs_signing_files']);
            $certsSigning = array();
            foreach( $list as $file) {
                $path = \jApp::configPath('saml/certs/'.$file);
                if (!file_exists($path)) {
                    throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.idp.key', array($path)));
                }
                $certsSigning[] = file_get_contents($path);
            }
            $list = preg_split('/ *, */', $idpConfig['certs_encryption_files']);
            $certsEncryption = array();
            foreach( $list as $file) {
                $path = \jApp::configPath('saml/certs/'.$file);
                if (!file_exists($path)) {
                    throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.missing.idp.cert', array($path)));
                }
                $certsEncryption[] = file_get_contents($path);
            }
        }

        $bindings = array(
            'http-post' => Constants::BINDING_HTTP_POST,
            'http-redirect' => Constants::BINDING_HTTP_REDIRECT,
            'http-artifact' => Constants::BINDING_HTTP_ARTIFACT,
            'soap' => Constants::BINDING_SOAP,
            'deflate' => Constants::BINDING_DEFLATE,
        );
        if (!isset($bindings[$idpConfig['singleSignOnServiceBinding']])) {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.bad.parameter', array('singleSignOnServiceBinding')));
        }

        if (!isset($bindings[$idpConfig['singleLogoutServiceBinding']])) {
            throw new \Exception(jLocale::get('saml~auth.authentication.error.saml.bad.parameter', array('singleLogoutServiceBinding')));
        }


        // Identity Provider Data that we want connect with our SP
        $identityProvider = array(
            'entityId' => $idpConfig['entityId'],
            'singleSignOnService' => array(
                'url' => $idpConfig['singleSignOnServiceUrl'],
                'binding' => $bindings[$idpConfig['singleSignOnServiceBinding']],
            ),
            'singleLogoutService' => array(
                'url' => $idpConfig['singleLogoutServiceUrl'],
                'responseUrl' => $idpConfig['singleLogoutServiceResponseUrl'],
                'binding' => $bindings[$idpConfig['singleLogoutServiceBinding']],
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
     * Gives the SAML attribute that contains the user login
     *
     * @return string
     */
    function getLoginAttribute() {
        return $this->loginAttribute;
    }

    /**
     * @return array
     */
    function getAttributesMapping() {
        return $this->attributesMapping;
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
