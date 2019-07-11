<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\CoreBundle\Fixture\FixtureInterface;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Service\ModuleConfiguration;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var ModuleConfiguration
     */
    private $moduleConfiguration;

    /**
     * PHP constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @param ModuleConfiguration $moduleConfiguration
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $encoder,
        ModuleConfiguration $moduleConfiguration
    ) {
        $this->entityManager = $entityManager;
        $this->moduleConfiguration = $moduleConfiguration;
        $this->encoder = $encoder;
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
    public function load(OutputInterface $output) : void
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
            ->setPassword($this->encoder->encodePassword($object, $data['password']))
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
    public function remove(OutputInterface $output) : void
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
     * @return GenericUser|null
     */
    private function findObject(string $identifier): ?GenericUser
    {
        /** @var GenericUser $object */
        $object = $this->moduleConfiguration->getRepository()->findOneBy(['username' => $identifier]);

        return $object;
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
