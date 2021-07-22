<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
