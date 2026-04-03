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
use Spipu\UserBundle\Event\PasswordValidationEvent;
use Spipu\UserBundle\Exception\PasswordPolicyException;
use Spipu\UserBundle\Event\UserEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserManager
{
    private EventDispatcherInterface $eventDispatcher;
    private UserConfiguration $userConfiguration;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        UserConfiguration $userConfiguration
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->userConfiguration = $userConfiguration;
    }

    /**
     * @throws PasswordPolicyException
     */
    public function validatePassword(string $password): void
    {
        $minLength = $this->userConfiguration->getSecurityPasswordMinLength();

        if (mb_strlen($password) < $minLength) {
            throw new PasswordPolicyException('spipu.user.error.password_too_short');
        }

        $event = new PasswordValidationEvent($password);
        $this->eventDispatcher->dispatch($event, PasswordValidationEvent::EVENT_CODE);
    }

    public function enableUser(UserInterface $user): void
    {
        $user->setActive(true);
        $user->setNbTryLogin(0);

        $event = new UserEvent($user, 'enable');
        $this->eventDispatcher->dispatch($event, $event->getEventCode());
    }

    public function disableUser(UserInterface $user): void
    {
        $user->setActive(false);

        $event = new UserEvent($user, 'disable');
        $this->eventDispatcher->dispatch($event, $event->getEventCode());
    }
}
