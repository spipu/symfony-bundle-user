<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Command;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Command\DisableUserCommand;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\UserBundle\Tests\Unit\Service\UserManagerTest;
use Symfony\Component\Console\Command\Command;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(DisableUserCommand::class)]
class DisableUserCommandTest extends TestCase
{
    public function testDisableSuccess(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setUsername('john');
        $user->setEmail('john@test.fr');
        $user->setPassword('encoded');
        $user->setActive(true);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->method('findOneBy')
            ->with(['username' => 'john'])
            ->willReturn($user);

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->once())->method('flush');

        $command = new DisableUserCommand($userRepository, UserManagerTest::getService($this), $entityManager);
        $this->assertSame('spipu:user:disable', $command->getName());

        $inputMock = SymfonyMock::getConsoleInput($this, ['username' => 'john']);
        $outputMock = SymfonyMock::getConsoleOutput($this);

        $result = $command->run($inputMock, $outputMock);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertFalse($user->getActive());

        $output = SymfonyMock::getConsoleOutputResult();
        $this->assertSame('Disable User', $output[0]);
        $this->assertStringContainsString('john', $output[1]);
        $this->assertStringContainsString('Done', $output[count($output) - 1]);
    }

    public function testDisableUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->method('findOneBy')
            ->with(['username' => 'unknown'])
            ->willReturn(null);

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->never())->method('flush');

        $command = new DisableUserCommand($userRepository, UserManagerTest::getService($this), $entityManager);

        $inputMock = SymfonyMock::getConsoleInput($this, ['username' => 'unknown']);
        $outputMock = SymfonyMock::getConsoleOutput($this);

        $result = $command->run($inputMock, $outputMock);

        $this->assertSame(Command::FAILURE, $result);

        $output = SymfonyMock::getConsoleOutputResult();
        $this->assertStringContainsString('Error', $output[count($output) - 1]);
    }
}
