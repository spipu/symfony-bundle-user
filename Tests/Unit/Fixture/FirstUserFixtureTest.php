<?php
namespace Spipu\UserBundle\Tests\Unit\Fixture;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Fixture\FixtureInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Fixture\FirstUserFixture;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;

class FirstUserFixtureTest extends TestCase
{
    public function testBasic()
    {
        $repository = $this->createMock(UserRepository::class);

        $fixture = new FirstUserFixture(
            SymfonyMock::getEntityManager($this),
            SymfonyMock::getUserPasswordEncoder($this),
            ModuleConfigurationTest::getService($this, true, true),
            $repository
        );

        $this->assertInstanceOf(FixtureInterface::class, $fixture);

        $this->assertSame('first-user', $fixture->getCode());
        $this->assertSame(10, $fixture->getOrder());
    }

    public function testLoadFirst()
    {
        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('persist')
            ->willReturnCallback(
                function ($object) {
                    /** @var UserInterface $object */
                    $this->assertInstanceOf(UserInterface::class, $object);
                    $this->assertSame('admin', $object->getUserIdentifier());
                    $this->assertSame('admin@admin.fr', $object->getEmail());
                    $this->assertSame('encoded_password', $object->getPassword());
                    $this->assertSame(['ROLE_SUPER_ADMIN'], $object->getRoles());
                }
            );

        $encoder = SymfonyMock::getUserPasswordEncoder($this);

        $configuration = ModuleConfigurationTest::getService($this, true, true);

        $repository = $this->createMock(UserRepository::class);
        $repository->expects($this->once())->method('findOneBy')->willReturn(null);

        $fixture = new FirstUserFixture($entityManager, $encoder, $configuration, $repository);

        $fixture->load(SymfonyMock::getConsoleOutput($this));

        $this->assertSame(
            ['Add Admin User'],
            SymfonyMock::getConsoleOutputResult()
        );
    }

    public function testLoadAfter()
    {
        $user = SpipuUserMock::getUserEntity(42);

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->never())->method('flush');
        $entityManager->expects($this->never())->method('persist');

        $encoder = SymfonyMock::getUserPasswordEncoder($this);

        $configuration = ModuleConfigurationTest::getService($this, true, true);

        $repository = $this->createMock(UserRepository::class);
        $repository->expects($this->once())->method('findOneBy')->willReturn($user);

        $fixture = new FirstUserFixture($entityManager, $encoder, $configuration, $repository);

        $fixture->load(SymfonyMock::getConsoleOutput($this));

        $this->assertSame(
            ['Add Admin User', '=> Already added'],
            SymfonyMock::getConsoleOutputResult()
        );
    }

    public function testRemoveFirst()
    {
        $user = SpipuUserMock::getUserEntity(42);

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('remove')->with($user);

        $encoder = SymfonyMock::getUserPasswordEncoder($this);

        $configuration = ModuleConfigurationTest::getService($this, true, true);

        $repository = $this->createMock(UserRepository::class);
        $repository->expects($this->once())->method('findOneBy')->willReturn($user);

        $fixture = new FirstUserFixture($entityManager, $encoder, $configuration, $repository);

        $fixture->remove(SymfonyMock::getConsoleOutput($this));

        $this->assertSame(
            ['Remove Admin User'],
            SymfonyMock::getConsoleOutputResult()
        );
    }

    public function testRemoveAfter()
    {
        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->never())->method('flush');
        $entityManager->expects($this->never())->method('remove');

        $encoder = SymfonyMock::getUserPasswordEncoder($this);

        $configuration = ModuleConfigurationTest::getService($this, true, true);

        $repository = $this->createMock(UserRepository::class);
        $repository->expects($this->once())->method('findOneBy')->willReturn(null);

        $fixture = new FirstUserFixture($entityManager, $encoder, $configuration, $repository);

        $fixture->remove(SymfonyMock::getConsoleOutput($this));

        $this->assertSame(
            ['Remove Admin User', '=> Already removed'],
            SymfonyMock::getConsoleOutputResult()
        );
    }
}
