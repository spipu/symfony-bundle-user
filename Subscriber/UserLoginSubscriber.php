<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Subscriber;

use Spipu\UserBundle\Entity\UserInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class UserLoginSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * UserLoginSubscriber constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

    /**
     * @param LoginSuccessEvent $event
     * @return void
     */
    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var UserInterface $user */
        $user = $event->getUser();
        $user->setTokenDate(null);
        $user->setNbTryLogin(0);
        $user->setNbLogin($user->getNbLogin() + 1);
        $this->entityManager->flush();
    }

    /**
     * @param LoginFailureEvent $event
     * @return void
     */
    public function onLoginFailed(LoginFailureEvent $event): void
    {
        $passport = $event->getPassport();

        if (null === $passport) {
            return;
        }
        $badges = $passport->getBadges();
        if (is_array($passport->getBadges()) && isset($badges[UserBadge::class])) {
            /** @var UserInterface $user */
            $user = $badges[UserBadge::class]->getUser();
            $user->setNbTryLogin($user->getNbTryLogin() + 1);

            $this->entityManager->flush();
        }
    }
}
