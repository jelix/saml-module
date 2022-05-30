<?php

/**
 * @author    Laurent Jouanneau
 * @copyright 2021 3liz
 *
 * @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
 */

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
}
