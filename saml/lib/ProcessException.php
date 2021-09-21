<?php
/**
 * @author  Laurent Jouanneau
 * @copyright  2019-2021 3Liz
 * @licence  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public Licence, see LICENCE file
 */
namespace Jelix\Saml;

use OneLogin\Saml2\Auth;

class ProcessException extends \DomainException
{
    protected $errors = array();

    function __construct(Auth $auth, $code = 0)
    {
        $errors = $auth->getErrors();
        $message = implode(', ', $errors)."\n".$auth->getLastErrorReason();
        parent::__construct($message, $code);
    }

    function getSamlErrors()
    {
        return $this->errors;
    }
}
