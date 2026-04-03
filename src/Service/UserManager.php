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

namespace Spipu\UserBundle\Service;

use Spipu\UserBundle\Entity\UserInterface;

class UserManager
{
    public function enableUser(UserInterface $user): void
    {
        $user->setActive(true);
        $user->setNbTryLogin(0);
    }

    public function disableUser(UserInterface $user): void
    {
        $user->setActive(false);
    }
}
