<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UserBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableUserCommand extends Command
{
    private UserRepository $userRepository;
    private UserManager $userManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserRepository $userRepository,
        UserManager $userManager,
        EntityManagerInterface $entityManager,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setName('spipu:user:disable')
            ->setDescription('Disable a user account.')
            ->setHelp('This command allows you to disable a user account')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the user to disable');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = (string) $input->getArgument('username');

        $output->writeln('Disable User');
        $output->writeln('  - Username: ' . $username);

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user === null) {
            $output->writeln('  => Error: user not found');
            return self::FAILURE;
        }

        $output->writeln('  - Email:    ' . $user->getEmail());
        $output->writeln('  - Active:   ' . ($user->getActive() ? 'yes' : 'no'));

        $this->userManager->disableUser($user);
        $this->entityManager->flush();

        $output->writeln('  => Done');

        return self::SUCCESS;
    }
}
