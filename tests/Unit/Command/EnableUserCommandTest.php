<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Command\EnableUserCommand;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\UserManager;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Symfony\Component\Console\Command\Command;

class EnableUserCommandTest extends TestCase
{
    public function testEnableSuccess(): void
    {
        $user = SpipuUserMock::getUserEntity(1);
        $user->setUsername('john');
        $user->setEmail('john@test.fr');
        $user->setPassword('encoded');
        $user->setActive(false);
        $user->setNbTryLogin(15);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->method('findOneBy')
            ->with(['username' => 'john'])
            ->willReturn($user);

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->once())->method('flush');

        $command = new EnableUserCommand($userRepository, new UserManager(), $entityManager);
        $this->assertSame('spipu:user:enable', $command->getName());

        $inputMock = SymfonyMock::getConsoleInput($this, ['username' => 'john']);
        $outputMock = SymfonyMock::getConsoleOutput($this);

        $result = $command->run($inputMock, $outputMock);

        $this->assertSame(Command::SUCCESS, $result);
        $this->assertTrue($user->getActive());
        $this->assertSame(0, $user->getNbTryLogin());

        $output = SymfonyMock::getConsoleOutputResult();
        $this->assertSame('Enable User', $output[0]);
        $this->assertStringContainsString('john', $output[1]);
        $this->assertStringContainsString('Done', $output[count($output) - 1]);
    }

    public function testEnableUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->method('findOneBy')
            ->with(['username' => 'unknown'])
            ->willReturn(null);

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->never())->method('flush');

        $command = new EnableUserCommand($userRepository, new UserManager(), $entityManager);

        $inputMock = SymfonyMock::getConsoleInput($this, ['username' => 'unknown']);
        $outputMock = SymfonyMock::getConsoleOutput($this);

        $result = $command->run($inputMock, $outputMock);

        $this->assertSame(Command::FAILURE, $result);

        $output = SymfonyMock::getConsoleOutputResult();
        $this->assertStringContainsString('Error', $output[count($output) - 1]);
    }
}
