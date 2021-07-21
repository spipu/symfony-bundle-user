<?php
declare(strict_types=1);

namespace Spipu\UserBundle\Security;

use Spipu\UserBundle\Entity\UserInterface as SpipuUserInterface;
use Spipu\UserBundle\Exception\UnactivatedAccountException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    /**
     * @param UserInterface $user
     * @return void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof SpipuUserInterface) {
            return;
        }

        if (!$user->getActive() || $user->getPassword() === null) {
            throw new UnactivatedAccountException('Unactivated Account');
        }
    }

    /**
     * @param UserInterface $user
     * @return void
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof SpipuUserInterface) {
            return;
        }

        if (!$user->getActive() || $user->getPassword() === null) {
            throw new UnactivatedAccountException('Unactivated Account');
        }
    }
}
