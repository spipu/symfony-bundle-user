<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Event\PasswordValidationEvent;
use Spipu\UserBundle\Exception\PasswordPolicyException;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Event\UserEvent;
use Spipu\UserBundle\Service\UserManager;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserManagerTest extends TestCase
{
    public static function getService(TestCase $testCase, array $configValues = []): UserManager
    {
        $eventDispatcher = SymfonyMock::getEventDispatcher($testCase);
        $userConfiguration = UserConfigurationTest::getService($testCase, $configValues);

        return new UserManager($eventDispatcher, $userConfiguration);
    }

    public function testEnableUser(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(false);
        $user->setNbTryLogin(15);

        $service = self::getService($this);
        $service->enableUser($user);

        $this->assertTrue($user->getActive());
        $this->assertSame(0, $user->getNbTryLogin());
    }

    public function testEnableUserAlreadyActive(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setNbTryLogin(3);

        $service = self::getService($this);
        $service->enableUser($user);

        $this->assertTrue($user->getActive());
        $this->assertSame(0, $user->getNbTryLogin());
    }

    public function testEnableUserEvent(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(false);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UserEvent::class),
                $this->equalTo('spipu.user.action.enable')
            );

        $userConfiguration = UserConfigurationTest::getService($this);
        $service = new UserManager($eventDispatcher, $userConfiguration);
        $service->enableUser($user);
    }

    public function testDisableUser(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);

        $service = self::getService($this);
        $service->disableUser($user);

        $this->assertFalse($user->getActive());
    }

    public function testDisableUserAlreadyInactive(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(false);

        $service = self::getService($this);
        $service->disableUser($user);

        $this->assertFalse($user->getActive());
    }

    public function testDisableUserEvent(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UserEvent::class),
                $this->equalTo('spipu.user.action.disable')
            );

        $userConfiguration = UserConfigurationTest::getService($this);
        $service = new UserManager($eventDispatcher, $userConfiguration);
        $service->disableUser($user);
    }

    public function testValidatePasswordOk(): void
    {
        $service = self::getService($this, ['user.security.password_min_length' => 10]);
        $service->validatePassword('1234567890');
        $this->assertTrue(true);
    }

    public function testValidatePasswordTooShort(): void
    {
        $service = self::getService($this, ['user.security.password_min_length' => 10]);

        $this->expectException(PasswordPolicyException::class);
        $service->validatePassword('123456789');
    }

    public function testValidatePasswordMinimumEnforced(): void
    {
        // Even if config is set to 1, minimum is 8
        $service = self::getService($this, ['user.security.password_min_length' => 1]);

        $this->expectException(PasswordPolicyException::class);
        $service->validatePassword('1234567');
    }

    public function testValidatePasswordEvent(): void
    {
        $userConfiguration = UserConfigurationTest::getService($this, ['user.security.password_min_length' => 10]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(PasswordValidationEvent::class),
                $this->equalTo(PasswordValidationEvent::EVENT_CODE)
            );

        $service = new UserManager($eventDispatcher, $userConfiguration);
        $service->validatePassword('1234567890');
    }

}
