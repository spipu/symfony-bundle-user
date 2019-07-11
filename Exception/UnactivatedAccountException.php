<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class UnactivatedAccountException extends AccountStatusException
{
    /**
     * @return string
     */
    public function getMessageKey()
    {
        return 'Unactivated Account';
    }
}
