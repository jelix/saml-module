<?php
/**
 * @author    Laurent Jouanneau
 * @copyright 2021-2026 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

use Jelix\Saml\Configuration;

class samladminListener extends jEventListener
{
    public function onmasteradminGetMenuContent($event)
    {
        if (jAcl2::check('saml.config.access')) {

            $bloc = new masterAdminMenuItem(
                'samlconfig',
                jLocale::get('samladmin~admin.config.title'),
                jUrl::get('samladmin~config:index'),
                0 , 'system');

            // Add the bloc
            $event->add($bloc);
        }
    }

    function onmasterAdminGetDashboardWidget($event)
    {
        $config = new Configuration(false);

        $spCertValidity = $config->checkCertificate($config->getSpCertificate());
        $idpSigningCertValidity = $config->checkCertificate($config->getIdpSigningCertificate());
        $idpEncryptionCertValidity = $config->checkCertificate($config->getIdpEncryptionCertificate());

        if ($spCertValidity[0] == Configuration::CERT_VALID
            && $idpSigningCertValidity[0] == Configuration::CERT_VALID
            && $idpEncryptionCertValidity[0] == Configuration::CERT_VALID) {
            return;
        }

        $html = "<ul>";
        $msgCount = 0;
        if ($spCertValidity[0] != Configuration::CERT_VALID) {
            $html .= '<li>'.$config->getHumanMessageForValidity($spCertValidity, Configuration::CERT_TYPE_SP).'</li>';
            $msgCount++;
        }
        if ($idpSigningCertValidity[0] != Configuration::CERT_VALID) {
            $html .= '<li>'.$config->getHumanMessageForValidity($idpSigningCertValidity, Configuration::CERT_TYPE_IDP_SIGNING).'</li>';
            $msgCount++;
        }
        if ($idpEncryptionCertValidity[0] != Configuration::CERT_VALID) {
            $html .= '<li>'.$config->getHumanMessageForValidity($idpEncryptionCertValidity, Configuration::CERT_TYPE_IDP_ENCRYPTION).'</li>';
            $msgCount++;
        }
        $html .= '</ul>';

        if (jAcl2::check('saml.config.access')) {
            if ($msgCount > 1) {
                $html .= '<p>'.jLocale::get('samladmin~admin.error.saml.certs.admin.instructions').'</p>';
            }
            else {
                $html .= '<p>'.jLocale::get('samladmin~admin.error.saml.cert.admin.instructions').'</p>';
            }
        }
        else if ($msgCount > 1) {
            $html .= '<p>'.jLocale::get('samladmin~admin.error.saml.certs.instructions').'</p>';
        }
        else {
            $html .= '<p>'.jLocale::get('samladmin~admin.error.saml.cert.instructions').'</p>';
        }

        $box = new masterAdminDashboardWidget();
        $box->title = jLocale::get('samladmin~admin.widget.dashboard.title', array($config->getIdpLabel()));
        $box->content = $html;
        $event->add($box);
    }

    function onjauthdbAdminEditCreate(jEvent $event)
    {
        /** @var jFormsBase $form */
        $form = $event->form;

        if ($event->form->getControl('jcommFirstStatus')) {
            // in the case where the jcommunity module is installed

            $config = new \Jelix\Saml\Configuration(false);
            if ($config->isAllowingSAMLAccountToUseLocalPassword()) {
                $event->add('<p>'.jLocale::get('samladmin~admin.auth.account.create.email.password.with.saml').'</p>');
            }
            else {
                $event->add('<p>'.jLocale::get('samladmin~admin.auth.account.create.email.password.no.saml').'</p>');
            }
        }
    }

    function onjauthdbAdminGetViewInfo(jEvent $event)
    {
        // peut utiliser mot de passe :
        $login = $event->form->getData('login');
        /** @var \samlAuthDriver $samlDriver */
        $samlDriver = \jAuth::getDriver();
        $permissions = $samlDriver->getAuthenticationPermissions($login);
        if (($permissions & $samlDriver::AUTH_PASSWORD_ALLOWED) && ($permissions & $samlDriver::AUTH_SAML_ALLOWED)) {
            $explanation = jLocale::get('samladmin~admin.auth.account.infos.permissions.with_saml_login');
        }
        else if ($permissions & $samlDriver::AUTH_PASSWORD_ALLOWED) {
            $explanation = jLocale::get('samladmin~admin.auth.account.infos.permissions.with_login');
        }
        else if ($permissions & $samlDriver::AUTH_SAML_ALLOWED) {
            $explanation = jLocale::get('samladmin~admin.auth.account.infos.permissions.with_saml');
        }
        else {
            $explanation = jLocale::get('samladmin~admin.auth.account.infos.permissions.none');
        }

        $content = '<h4>SAML</h4><p>'.htmlspecialchars($explanation).'</p>';
        $event->add($content);
    }
}
