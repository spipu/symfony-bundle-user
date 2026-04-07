<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Service\UserTokenManager;
use Spipu\UserBundle\Tests\SpipuUserMock;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(UserTokenManager::class)]
class UserTokenManagerTest extends TestCase
{
    public function testService(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setEmail('mock_email')
            ->setUsername('mock_username');

        $user->setCreatedAtValue();
        $user->setUpdatedAtValue();

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->exactly(2))->method('persist')->with($user);
        $entityManager->expects($this->exactly(2))->method('flush');

        $userConfiguration = UserConfigurationTest::getService($this, [
            'user.security.token_expiration' => 12,
        ]);

        $service = new UserTokenManager($entityManager, 'secret_mock', $userConfiguration);

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

    public function testTokenExpired(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setEmail('mock_email')
            ->setUsername('mock_username');

        $user->setCreatedAtValue();
        $user->setUpdatedAtValue();

        $entityManager = SymfonyMock::getEntityManager($this);

        // Token expires after 1 hour
        $userConfiguration = UserConfigurationTest::getService($this, [
            'user.security.token_expiration' => 1,
        ]);

        $service = new UserTokenManager($entityManager, 'secret_mock', $userConfiguration);

        $token = $service->generate($user);
        $this->assertTrue($service->isValid($user, $token));

        // Simulate token created 2 hours ago
        $user->setTokenDate(new \DateTime('-2 hours'));
        $this->assertFalse($service->isValid($user, $token));
    }
}
