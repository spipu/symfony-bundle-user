<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;

/**
 * @method UserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterface[]    findAll()
 * @method UserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
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
     * @param string $username
     * @return User|null
     */
    public function loadUserByIdentifier(string $username): ?User
    {
        try {
            return $this->createQueryBuilder('u')
                ->where('u.username = :username OR u.email = :email')
                ->setParameter('username', $username)
                ->setParameter('email', $username)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (\Exception $e) {
            return null;
        }
    }
}
