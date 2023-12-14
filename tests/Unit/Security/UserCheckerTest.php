<?php
namespace Spipu\UserBundle\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\UserBundle\Exception\UnactivatedAccountException;
use Spipu\UserBundle\Security\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    public function testPreGoodUserEnabled()
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(true);

        $service = new UserChecker();
        $service->checkPreAuth($user);
        $this->assertTrue(true);
    }

    public function testPreGoodUserDisabled()
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

    public function testPreBadUser()
    {
        $user = $this->createMock(UserInterface::class);

        $service = new UserChecker();
        $service->checkPreAuth($user);

        $this->assertTrue(true);
    }

    public function testPostGoodUserEnabled()
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(true);

        $service = new UserChecker();
        $service->checkPostAuth($user);
        $this->assertTrue(true);
    }

    public function testPostGoodUserDisabled()
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user->setPassword('encoded_password');
        $user->setActive(false);

        $this->expectException(UnactivatedAccountException::class);

        $service = new UserChecker();
        $service->checkPostAuth($user);
    }

    public function testPostBadUser()
    {
        $user = $this->createMock(UserInterface::class);

        $service = new UserChecker();
        $service->checkPostAuth($user);
        $this->assertTrue(true);
    }
}
