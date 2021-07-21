<?php
declare(strict_types=1);

namespace Spipu\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method UserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterface[]    findAll()
 * @method UserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     * @param ModuleConfigurationInterface $moduleConfiguration
     */
    public function __construct(
        ManagerRegistry $registry,
        ModuleConfigurationInterface $moduleConfiguration
    ) {
        parent::__construct($registry, trim($moduleConfiguration->getEntityClassName(), '\\'));
    }

    /**
     * Loads the user for the given username.
     * This method must return null if the user is not found.
     *
     * @param string $identifier
     * @return UserInterface|null
     */
    public function loadUserByIdentifier(string $identifier): ?UserInterface //@codingStandardsIgnoreLine
    {
        try {
            return $this->createQueryBuilder('u')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $identifier)
                ->setParameter('email', $identifier)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $username
     * @return UserInterface|null
     */
    public function loadUserByUsername(string $username): ?UserInterface //@codingStandardsIgnoreLine
    {
        return $this->loadUserByIdentifier($username);
    }
}
