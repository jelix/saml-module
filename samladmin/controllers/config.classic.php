<?php
/**
 * SAML administration.
 *
 * @author    Laurent Jouanneau
 * @copyright 2021-2026 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */
use Jelix\Saml\Configuration;

class configCtrl extends jController
{
    // Configure access via jacl2 rights management
    public $pluginParams = array(
        '*' => array('jacl2.right' => 'saml.config.access'),
    );


    /**
     * Display a summary of the information taken from the configuration file.
     */
    public function index()
    {
        $rep = $this->getResponse('html');

        $tpl = new jTpl();

        $config = new Configuration(false);

        try {
            $config->checkSpConfig();
            $tpl->assign('sp_config_ok', true);
        }
        catch(\Exception $e) {
            $tpl->assign('sp_config_ok', false);
        }

        $spCertValidity = $config->checkCertificate($config->getSpCertificate());
        if ($spCertValidity[0] == Configuration::CERT_VALID) {
            $tpl->assign('sp_cert_error', '');
        }
        else {
            $tpl->assign('sp_cert_error', $config->getHumanMessageForValidity($spCertValidity, Configuration::CERT_TYPE_SP));
        }

        try {
            $config->checkIdpConfig();
            $tpl->assign('idp_config_ok', true);
        }
        catch(\Exception $e) {
            $tpl->assign('idp_config_ok', false);
        }

        $idpSigningCertValidity = $config->checkCertificate($config->getIdpSigningCertificate());
        if ($idpSigningCertValidity[0] == Configuration::CERT_VALID) {
            $tpl->assign('idp_signing_cert_error', '');
        }
        else {
            $tpl->assign('idp_signing_cert_error', $config->getHumanMessageForValidity($idpSigningCertValidity, Configuration::CERT_TYPE_IDP_SIGNING));
        }

        try {
            $config->checkAttrConfig();
            $tpl->assign('attr_config_ok', true);
        }
        catch(\Exception $e) {
            $tpl->assign('attr_config_ok', false);
        }

        $idpEncryptionCertValidity = $config->checkCertificate($config->getIdpEncryptionCertificate());
        if ($idpEncryptionCertValidity[0] == Configuration::CERT_VALID) {
            $tpl->assign('idp_encryption_cert_error', '');
        }
        else {
            $tpl->assign('idp_encryption_cert_error', $config->getHumanMessageForValidity($idpEncryptionCertValidity, Configuration::CERT_TYPE_IDP_ENCRYPTION));
        }

        $tpl->assign('sp_metadata_url', jUrl::getFull('saml~endpoint:metadata'));
        $tpl->assign('sp_metadata_url_dnl', jUrl::getFull('saml~endpoint:metadata' , ['download'=>1]));
        $tpl->assign('sp_acs_url', jUrl::getFull('saml~endpoint:acs'));
        $tpl->assign('sp_sls_url', jUrl::getFull('saml~endpoint:sls'));

        $rep->body->assign('MAIN', $tpl->fetch('config'));
        $rep->body->assign('selectedMenuItem', 'samlconfig');
        $rep->addCSSLink(jUrl::get('samladmin~config:asset', array('file'=>'admin.css')));
        return $rep;
    }


    public function asset() {

        $rep = $this->getResponse('binary');
        $rep->doDownload = false;
        $dir = __DIR__.'/../www/';
        $rep->fileName = realpath($dir.str_replace('..', '', $this->param('file')));

        if (!is_file($rep->fileName)) {
            $rep = $this->getResponse('html', true);
            $rep->bodyTpl = 'jelix~404.html';
            $rep->setHttpStatus('404', 'Not Found');
            return $rep;
        }
        $rep->mimeType = jFile::getMimeTypeFromFilename($rep->fileName);
        return $rep;
    }
}
