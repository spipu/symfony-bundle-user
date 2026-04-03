<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Service\UserManager;
use Spipu\UserBundle\Tests\SpipuUserMock;

class UserManagerTest extends TestCase
{
    public function testEnableUser(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(false);
        $user->setNbTryLogin(15);

        $service = new UserManager();
        $service->enableUser($user);

        $this->assertTrue($user->getActive());
        $this->assertSame(0, $user->getNbTryLogin());
    }

    public function testEnableUserAlreadyActive(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);
        $user->setNbTryLogin(3);

        $service = new UserManager();
        $service->enableUser($user);

        $this->assertTrue($user->getActive());
        $this->assertSame(0, $user->getNbTryLogin());
    }

    public function testDisableUser(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(true);

        $service = new UserManager();
        $service->disableUser($user);

        $this->assertFalse($user->getActive());
    }

    public function testDisableUserAlreadyInactive(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setActive(false);

        $service = new UserManager();
        $service->disableUser($user);

        $this->assertFalse($user->getActive());
    }
}
