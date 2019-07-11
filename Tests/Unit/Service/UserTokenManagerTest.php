<?php
namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Service\UserTokenManager;

class UserTokenManagerTest extends TestCase
{
    public function testService()
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setEmail('mock_email')
            ->setUsername('mock_username')
            ->setCreatedAtValue()
            ->setUpdatedAtValue();

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->exactly(2))->method('persist')->with($user);
        $entityManager->expects($this->exactly(2))->method('flush');

        $service = new UserTokenManager($entityManager, 'secret_mock');

        $this->assertSame(null, $user->getTokenDate());

        $token = $service->generate($user);
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getTokenDate());
        $this->assertSame(64, strlen($token));

        $this->assertTrue($service->isValid($user, $token));
        $this->assertFalse($service->isValid($user, 'bad_token'));

        $service->reset($user);
        $this->assertSame(null, $user->getTokenDate());
        $this->assertFalse($service->isValid($user, $token));
    }
}
