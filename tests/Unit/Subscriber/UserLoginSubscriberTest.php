<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Subscriber\UserLoginSubscriber;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\UserBundle\Tests\Unit\Service\UserManagerTest;
use Spipu\UserBundle\Tests\Unit\Service\UserConfigurationTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class UserLoginSubscriberTest extends TestCase
{
    private function getEntityManager(): EntityManagerInterface
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('flush');

        return $entityManager;
    }

    public function testGetSubscribedEvents(): void
    {
        $events = UserLoginSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(LoginSuccessEvent::class, $events);
        $this->assertArrayHasKey(LoginFailureEvent::class, $events);
    }

    public function testOnLoginSuccess(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setPassword('encoded');
        $user->setNbLogin(5);
        $user->setNbTryLogin(3);

        $userConfiguration = UserConfigurationTest::getService($this);
        $subscriber = new UserLoginSubscriber($this->getEntityManager(), $userConfiguration, UserManagerTest::getService($this));

        $passport = new SelfValidatingPassport(new UserBadge('test', function () use ($user) {
            return $user;
        }));
        $token = new UsernamePasswordToken($user, 'main', ['ROLE_USER']);
        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $event = new LoginSuccessEvent($authenticator, $passport, $token, new Request(), null, 'main');

        $subscriber->onLoginSuccess($event);

        $this->assertSame(0, $user->getNbTryLogin());
        $this->assertSame(6, $user->getNbLogin());
        $this->assertNull($user->getTokenDate());
    }

    public function testOnLoginFailedUnderLimit(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setPassword('encoded');
        $user->setNbTryLogin(3);

        $userConfiguration = UserConfigurationTest::getService($this, [
            'user.security.lock_enabled' => 1,
            'user.security.lock_max_attempts' => 10,
        ]);
        $subscriber = new UserLoginSubscriber($this->getEntityManager(), $userConfiguration, UserManagerTest::getService($this));

        $event = $this->createLoginFailureEvent($user);
        $subscriber->onLoginFailed($event);

        $this->assertSame(4, $user->getNbTryLogin());
        $this->assertTrue($user->getActive());
    }

    public function testOnLoginFailedAtLimit(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setPassword('encoded');
        $user->setNbTryLogin(9);

        $userConfiguration = UserConfigurationTest::getService($this, [
            'user.security.lock_enabled' => 1,
            'user.security.lock_max_attempts' => 10,
        ]);
        $subscriber = new UserLoginSubscriber($this->getEntityManager(), $userConfiguration, UserManagerTest::getService($this));

        $event = $this->createLoginFailureEvent($user);
        $subscriber->onLoginFailed($event);

        $this->assertSame(10, $user->getNbTryLogin());
        $this->assertFalse($user->getActive());
    }

    public function testOnLoginFailedOverLimit(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setPassword('encoded');
        $user->setNbTryLogin(15);

        $userConfiguration = UserConfigurationTest::getService($this, [
            'user.security.lock_enabled' => 1,
            'user.security.lock_max_attempts' => 10,
        ]);
        $subscriber = new UserLoginSubscriber($this->getEntityManager(), $userConfiguration, UserManagerTest::getService($this));

        $event = $this->createLoginFailureEvent($user);
        $subscriber->onLoginFailed($event);

        $this->assertSame(16, $user->getNbTryLogin());
        $this->assertFalse($user->getActive());
    }

    public function testOnLoginFailedLockDisabled(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setPassword('encoded');
        $user->setNbTryLogin(100);

        $userConfiguration = UserConfigurationTest::getService($this, [
            'user.security.lock_enabled' => 0,
        ]);
        $subscriber = new UserLoginSubscriber($this->getEntityManager(), $userConfiguration, UserManagerTest::getService($this));

        $event = $this->createLoginFailureEvent($user);
        $subscriber->onLoginFailed($event);

        $this->assertSame(101, $user->getNbTryLogin());
        $this->assertTrue($user->getActive());
    }

    public function testOnLoginFailedNoPassport(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never())->method('flush');

        $userConfiguration = UserConfigurationTest::getService($this);
        $subscriber = new UserLoginSubscriber($entityManager, $userConfiguration, UserManagerTest::getService($this));

        $authenticator = $this->createMock(AuthenticatorInterface::class);
        $event = new LoginFailureEvent(
            new AuthenticationException(),
            $authenticator,
            new Request(),
            null,
            'main',
            null
        );

        $subscriber->onLoginFailed($event);
        $this->assertTrue(true);
    }

    private function createLoginFailureEvent(UserInterface $user): LoginFailureEvent
    {
        $passport = new SelfValidatingPassport(new UserBadge('test', function () use ($user) {
            return $user;
        }));

        $authenticator = $this->createMock(AuthenticatorInterface::class);

        return new LoginFailureEvent(
            new AuthenticationException(),
            $authenticator,
            new Request(),
            null,
            'main',
            $passport
        );
    }
}
