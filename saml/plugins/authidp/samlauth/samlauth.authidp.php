<?php
/**
 * @author   Laurent Jouanneau
 * @copyright 2026 Laurent Jouanneau
 * @link     https://jelix.org
 * @licence MIT
 */

use Jelix\Authentication\Core\IdentityProviderInterface;

class samlauthIdentityProvider implements IdentityProviderInterface
{

    protected $config;

    public function __construct(array $options)
    {
        $this->config = $options;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return 'samlauth';
    }

    /**
     * @inheritDoc
     */
    public function getLoginUrl()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getLogoutUrl()
    {
        return jUrl::get('saml~auth:logout');
    }

    /**
     * @inheritDoc
     */
    public function getHtmlLoginForm(\jRequest $request) {
        return jZone::get('saml~samlform');
    }

    /**
     * @inheritDoc
     */
    public function checkSessionValidity ($request, $authUser, $authRequired)
    {

        if ($authRequired && !$authUser) {
            // if this is an ajax request, we don't want redirection to a web page
            // so we shouldn't force authentication if we are not logged
            if ($request->isAjax()) {
                throw new \jHttp401UnauthorizedException(\jLocale::get('authcore~auth.error.not.authenticated'));
            }

            // we force authentication to SAML directly only if this is the desired behavior
            // else we trigger an exception so the login form will be displayed.
            $samlConf = jApp::config()->saml;
            if (isset($samlConf['forceSAMLAuthOnPrivatePage']) && $samlConf['forceSAMLAuthOnPrivatePage']) {
                return new jSelectorAct('saml~auth:login');
            }
            else {
                throw new \jHttp401UnauthorizedException(\jLocale::get('authcore~auth.error.not.authenticated'));
            }
        }

        return null;
    }
}
