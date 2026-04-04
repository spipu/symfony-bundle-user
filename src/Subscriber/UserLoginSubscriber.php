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

namespace Spipu\UserBundle\Subscriber;

use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Service\UserConfiguration;
use Spipu\UserBundle\Service\UserManager;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class UserLoginSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private UserConfiguration $userConfiguration;
    private UserManager $userManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserConfiguration $userConfiguration,
        UserManager $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->userConfiguration = $userConfiguration;
        $this->userManager = $userManager;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailed',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getUser();
        $user->setTokenDate(null);
        $user->setNbTryLogin(0);
        $user->setNbLogin($user->getNbLogin() + 1);
        $this->entityManager->flush();
    }

    public function onLoginFailed(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();

        if ($passport !== null) {
            $badges = $passport->getBadges();
            if (is_array($passport->getBadges()) && isset($badges[UserBadge::class])) {
                /** @var UserInterface $user */
                $user = $badges[UserBadge::class]->getUser();
                if (!$user->getActive()) {
                    return;
                }
                $user->setNbTryLogin($user->getNbTryLogin() + 1);

                if ($this->userConfiguration->hasSecurityLockEnabled()) {
                    $maxAttempts = $this->userConfiguration->getSecurityLockMaxAttempts();
                    if ($user->getNbTryLogin() >= $maxAttempts) {
                        $this->userManager->disableUser($user);
                    }
                }

                $this->entityManager->flush();
            }
        }
    }
}
