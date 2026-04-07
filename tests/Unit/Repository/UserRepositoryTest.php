<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(UserRepository::class)]
class UserRepositoryTest extends TestCase
{
    public function testRepository(): void
    {
        $configuration = ModuleConfigurationTest::getService($this, true, true);
        $repository = new UserRepository(SymfonyMock::getEntityRegistry($this), $configuration);

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }
}
