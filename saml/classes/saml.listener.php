<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2021-2026 3liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */

use Jelix\Authentication\Core\AuthSession\UserAccountInterface;
use Jelix\Authentication\Core\IdentityProviderInterface;
use Jelix\Authentication\Core\Workflow\Event\CheckAccountEvent;


class samlListener extends jEventListener{

    /**
     * @param CheckAccountEvent $event
     * @return void
     */
    function onAuthWorkflowStep($event)
    {
        if (!($event instanceof CheckAccountEvent)) {
            return;
        }

        $idpId = $event->getIdpId();
        if ($idpId != 'samlauth') {
            return;
        }

        // this is the opportunity to add information on the account


        /** @var \Jelix\Authentication\Core\AuthSession\AuthUser $user */
        $user = $event->getUserBeingAuthenticated();


    }

    /**
     * @param jEvent $event
     * @return void
     */
    function onAuthenticationLogin($event)
    {
        /** @var IdentityProviderInterface $idp */
        $idp = $event->identProvider;
        if ($idp->getId() != 'samlauth') {
            return;
        }
        $configuration = new \Jelix\Saml\Configuration();
        $saml = new Jelix\Saml\Saml($configuration);
        $saml->finishLoginProcess($event->user);
    }

}
