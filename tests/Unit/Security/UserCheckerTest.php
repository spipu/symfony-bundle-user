<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\UserBundle\Exception\UnactivatedAccountException;
use Spipu\UserBundle\Security\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    public function testPreGoodUserEnabled(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(true);

        $service = new UserChecker();
        $service->checkPreAuth($user);
        $this->assertTrue(true);
    }

    public function testPreGoodUserDisabled(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(false);

        $this->expectException(UnactivatedAccountException::class);

        try {
            $service = new UserChecker();
            $service->checkPreAuth($user);
        } catch (UnactivatedAccountException $e) {
            $this->assertSame('Unactivated Account', $e->getMessageKey());
            throw $e;
        }
    }

    public function testPreBadUser(): void
    {
        $user = $this->createMock(UserInterface::class);

        $service = new UserChecker();
        $service->checkPreAuth($user);

        $this->assertTrue(true);
    }

    public function testPostGoodUserEnabled(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(true);

        $service = new UserChecker();
        $service->checkPostAuth($user);
        $this->assertTrue(true);
    }

    public function testPostGoodUserDisabled(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(false);

        $this->expectException(UnactivatedAccountException::class);

        $service = new UserChecker();
        $service->checkPostAuth($user);
    }

    public function testPostBadUser(): void
    {
        $user = $this->createMock(UserInterface::class);

        $service = new UserChecker();
        $service->checkPostAuth($user);
        $this->assertTrue(true);
    }
}
