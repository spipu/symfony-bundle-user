<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Subscriber;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Service\ModuleConfiguration;
use Spipu\UserBundle\Subscriber\RememberMeSubscriber;
use Spipu\UserBundle\Tests\GenericUser;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class RememberMeSubscriberTest extends TestCase
{
    private function getSubscriber(bool $allowRememberMe): RememberMeSubscriber
    {
        return new RememberMeSubscriber(
            new ModuleConfiguration('MockUser', GenericUser::class, true, true, $allowRememberMe)
        );
    }

    private function getSpipuUser(): UserInterface
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setUsername('test');

        return $user;
    }

    private function getPassport(UserInterface $user, bool $withBadge = true): SelfValidatingPassport
    {
        $badges = $withBadge ? [(new RememberMeBadge())->enable()] : [];

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), function () use ($user) {
                return $user;
            }),
            $badges
        );
    }

    private function getEvent(SelfValidatingPassport $passport): LoginSuccessEvent
    {
        $token = new UsernamePasswordToken($passport->getUser(), 'main', ['ROLE_USER']);
        $authenticator = $this->createMock(AuthenticatorInterface::class);

        return new LoginSuccessEvent($authenticator, $passport, $token, new Request(), null, 'main');
    }

    public function testGetSubscribedEvents(): void
    {
        $events = RememberMeSubscriber::getSubscribedEvents();

        $this->assertSame(['onLoginSuccess', -48], $events[LoginSuccessEvent::class]);
    }

    public function testAllowed(): void
    {
        $passport = $this->getPassport($this->getSpipuUser());

        $this->getSubscriber(true)->onLoginSuccess($this->getEvent($passport));

        $this->assertTrue($passport->getBadge(RememberMeBadge::class)->isEnabled());
    }

    public function testNotAllowed(): void
    {
        $passport = $this->getPassport($this->getSpipuUser());

        $this->getSubscriber(false)->onLoginSuccess($this->getEvent($passport));

        $this->assertFalse($passport->getBadge(RememberMeBadge::class)->isEnabled());
    }

    public function testNotAllowedWithoutBadge(): void
    {
        $passport = $this->getPassport($this->getSpipuUser(), false);

        $this->getSubscriber(false)->onLoginSuccess($this->getEvent($passport));

        $this->assertFalse($passport->hasBadge(RememberMeBadge::class));
    }

    public function testNotAllowedOtherUser(): void
    {
        $passport = $this->getPassport(new InMemoryUser('other', 'password'));

        $this->getSubscriber(false)->onLoginSuccess($this->getEvent($passport));

        $this->assertTrue($passport->getBadge(RememberMeBadge::class)->isEnabled());
    }
}
