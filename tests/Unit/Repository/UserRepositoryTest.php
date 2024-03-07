<?php
namespace Spipu\UserBundle\Tests\Unit\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;

class UserRepositoryTest extends TestCase
{
    public function testRepository()
    {
        $configuration = ModuleConfigurationTest::getService($this, true, true);
        $repository = new UserRepository(SymfonyMock::getEntityRegistry($this), $configuration);

        $this->assertInstanceOf(ServiceEntityRepository::class, $repository);
    }
}
