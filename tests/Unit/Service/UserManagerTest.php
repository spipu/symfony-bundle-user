<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Event\UserEvent;
use Spipu\UserBundle\Service\UserManager;
use Spipu\UserBundle\Tests\SpipuUserMock;

class UserManagerTest extends TestCase
{
    public static function getService(TestCase $testCase): UserManager
    {
        $eventDispatcher = SymfonyMock::getEventDispatcher($testCase);

        return new UserManager($eventDispatcher);
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

        $eventDispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UserEvent::class),
                $this->equalTo('spipu.user.action.enable')
            );

        $service = new UserManager($eventDispatcher);
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

        $eventDispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UserEvent::class),
                $this->equalTo('spipu.user.action.disable')
            );

        $service = new UserManager($eventDispatcher);
        $service->disableUser($user);
    }
}
