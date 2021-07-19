<?php
namespace Spipu\UserBundle\Tests\Unit\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
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

        $this->assertNull($repository->loadUserByIdentifier('test'));

        /** @var MockObject $query */
        $query = $repository->createQueryBuilder('u')->getQuery();
        $query->method('getOneOrNullResult')->willThrowException(new \Exception('in mock'));

        $this->assertNull($repository->loadUserByIdentifier('test'));
    }
}
