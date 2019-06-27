<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use OneLogin\Saml2\Metadata;
use OneLogin\Saml2\Utils;

/**
 * Controller called by the Identity Provider
 */
class endpointCtrl extends jController {

    public $pluginParams = array(
        '*' => array('auth.required'=>false)
    );

    function metadata() {
        $xml = $this->getResponse('xml');

        $configuration = new \Jelix\Saml\Configuration($this->request);

        $samlSettings = $configuration->getSettings();

        $sp = $samlSettings->getSPData();
        $samlMetadata = Metadata::builder($sp);
        $xml->content = $samlMetadata;

        /*
        $samlMetadata = $settings->getSPMetadata();
        $errors = $settings->validateMetadata($metadata);
        if (!empty($errors)) {
            throw new Error(
                'Invalid SP metadata: '.implode(', ', $errors),
                Error::METADATA_SP_INVALID
            );
        }
         */

        $xml->sendXMLHeader = false;
        $xml->checkValidity = false;
        return $xml;
    }

    /**
     * SP Assertion Consumer Service Endpoint
     */
    function acs() {
        $configuration = new \Jelix\Saml\Configuration($this->request);

        $samlSettings = $configuration->getSettingsArray();
        $auth = new Auth($samlSettings);
        $auth->processResponse();

        $errors = $auth->getErrors();
        if (!empty($errors)) {
            return $this->error(implode(', ', $errors));
        }

        if (!$auth->isAuthenticated()) {
            return $this->error();
        }

        $_SESSION['samlUserdata'] = $auth->getAttributes();
        $_SESSION['IdPSessionIndex'] = $auth->getSessionIndex();
        $_SESSION['samlNameId'] = $auth->getNameId();
        $_SESSION['samlNameIdFormat'] = $auth->getNameIdFormat();
        $_SESSION['samlNameIdNameQualifier'] = $auth->getNameIdNameQualifier();
        $_SESSION['samlNameIdSPNameQualifier'] = $auth->getNameIdSPNameQualifier();

        if (isset($_POST['RelayState']) && Utils::getSelfURL() != $_POST['RelayState']) {
            /** @var jResponseRedirectUrl $rep */
            $rep = $this->getResponse('redirecturl');
            $rep->url = $_POST['RelayState'];
            return $rep;
        }

        $rep = $this->getResponse('html');
        $rep->title = jLocale::get('saml~auth.authentication.done');

        $attributes = $_SESSION['samlUserdata'];
        if (!empty($attributes)) {
            $html = '<h1>Attributes</h1>';
            $html .= '<table><thead><th>Names</th><th>values</th></thead><tbody>';
            foreach ($attributes as $attributeName => $attributeValues) {
                $html .= '<tr><td>'.htmlentities($attributeName).'</td><td><ul>';
                foreach ($attributeValues as $attributeValue) {
                    $html .= '<li>'.htmlentities($attributeValue).'</li>';
                }
                $html .= '</ul></td></tr>';
            }
            $html .= '</tbody></table>';
            if (!empty($_SESSION['IdPSessionIndex'])) {
                $html .= '<p>The SessionIndex of the IdP is: '.$_SESSION['IdPSessionIndex'].'</p>';
            }
        } else {
            $html = '';
        }

        $tpl = new jTpl();
        $tpl->assign('message', $html);
        $rep->body->assign('MAIN', $tpl->fetch('authenticated'));

        return $rep;
    }

    /**
     * SP Single Logout Service Endpoint
     *
     * @return jResponseHtml
     * @throws Error
     */
    function sls() {
        $configuration = new \Jelix\Saml\Configuration($this->request);
        $auth = new Auth($configuration->getSettingsArray());
        $auth->processSLO();

        $errors = $auth->getErrors();
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        $rep->title = 'Logged out';
        $tpl = new jTpl();

        if (empty($errors)) {
            jAuth::logout();
            $tpl->assign('error', '');
        } else {
            $tpl->assign('error', implode(', ', $errors));
        }
        $rep->body->assign('MAIN', $tpl->fetch('logout'));
        return $rep;
    }

    protected function error($error = '') {
        /** @var jResponseHtml $rep */
        $rep = $this->getResponse('htmlauth');
        if ($error) {
            $rep->setHttpStatus('503', 'Service Unavailable');
        }
        else {
            $rep->setHttpStatus('401', 'Unauthorized');
        }
        $rep->title = 'Authentication error';
        $tpl = new jTpl();
        $tpl->assign('error', $error);
        $rep->body->assign('MAIN', $tpl->fetch('notauthenticated'));
        return $rep;
    }
}

