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

namespace Spipu\UserBundle\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\CoreBundle\Fixture\FixtureInterface;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Users Creation
 */
class FirstUserFixture implements FixtureInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordHasherInterface
     */
    private $hasher;

    /**
     * @var ModuleConfigurationInterface
     */
    private $moduleConfiguration;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * PHP constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordHasherInterface $hasher
     * @param ModuleConfigurationInterface $moduleConfiguration
     * @param UserRepository $userRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,
        ModuleConfigurationInterface $moduleConfiguration,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->hasher = $hasher;
        $this->userRepository = $userRepository;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return 'first-user';
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    public function load(OutputInterface $output): void
    {
        $output->writeln("Add Admin User");
        $data = $this->getData();
        $object = $this->findObject($data['username']);
        if ($object) {
            $output->writeln("  => Already added");
            return;
        }

        $object = $this->moduleConfiguration->getNewEntity();
        $object
            ->setUsername($data['username'])
            ->setEmail($data['email'])
            ->setPassword($this->hasher->hashPassword($object, $data['password']))
            ->setFirstName($data['firstname'])
            ->setLastName($data['lastname'])
            ->setRoles($data['roles'])
            ->setActive($data['active']);

        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    public function remove(OutputInterface $output): void
    {
        $output->writeln("Remove Admin User");
        $data = $this->getData();
        $object = $this->findObject($data['username']);
        if (!$object) {
            $output->writeln("  => Already removed");
            return;
        }

        $this->entityManager->remove($object);
        $this->entityManager->flush();
    }

    /**
     * @param string $identifier
     * @return UserInterface|null
     */
    private function findObject(string $identifier): ?UserInterface
    {
        return $this->userRepository->findOneBy(['username' => $identifier]);
    }

    /**
     * @return array
     */
    protected function getData(): array
    {
        return [
            'username'  => 'admin',
            'email'     => 'admin@admin.fr',
            'password'  => 'password',
            'firstname' => 'Admin',
            'lastname'  => 'Admin',
            'roles'     => ['ROLE_SUPER_ADMIN'],
            'active'    => true,
        ];
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return 10;
    }
}
