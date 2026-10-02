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
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class RememberMeSubscriber implements EventSubscriberInterface
{
    private ModuleConfigurationInterface $moduleConfiguration;

    public function __construct(ModuleConfigurationInterface $moduleConfiguration)
    {
        $this->moduleConfiguration = $moduleConfiguration;
    }

    /**
     * @return array<string, array{0: string, 1: int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // After the badge is enabled (CheckRememberMeConditionsListener: -32)
            // and before the cookie is created (RememberMeListener: -64).
            LoginSuccessEvent::class => ['onLoginSuccess', -48],
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if ($this->moduleConfiguration->hasAllowRememberMe()) {
            return;
        }

        // Users of other firewalls (not managed by this bundle) are ignored.
        if (!$event->getUser() instanceof UserInterface) {
            return;
        }

        // Even if the firewall still has the remember_me option, no cookie is created.
        $passport = $event->getPassport();
        if ($passport->hasBadge(RememberMeBadge::class)) {
            /** @var RememberMeBadge $badge */
            $badge = $passport->getBadge(RememberMeBadge::class);
            $badge->disable();
        }
    }
}
