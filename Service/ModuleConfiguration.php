<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModuleConfiguration
{
    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityClassName;

    /**
     * @var UserRepository
     */
    private $entityRepository;

    /**
     * @var bool
     */
    private $allowAccountCreation;

    /**
     * @var bool
     */
    private $allowPasswordRecovery;

    /**
     * UserTokenService constructor.
     * @param string $entityName
     * @param string $entityClassName
     * @param UserRepository $entityRepository
     * @param bool $allowAccountCreation
     * @param bool $allowPasswordRecovery
     */
    public function __construct(
        string $entityName,
        string $entityClassName,
        UserRepository $entityRepository,
        bool $allowAccountCreation,
        bool $allowPasswordRecovery
    ) {
        $this->entityName = $entityName;
        $this->entityClassName = $entityClassName;
        $this->entityRepository = $entityRepository;
        $this->allowAccountCreation = $allowAccountCreation;
        $this->allowPasswordRecovery = $allowPasswordRecovery;
    }

    /**
     * @return bool
     */
    public function hasAllowAccountCreation(): bool
    {
        return $this->allowAccountCreation;
    }

    /**
     * @return bool
     */
    public function hasAllowPasswordRecovery(): bool
    {
        return $this->allowPasswordRecovery;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    /**
     * @return GenericUser
     */
    public function getNewEntity(): GenericUser
    {
        $className = $this->entityClassName;

        return new $className();
    }

    /**
     * @return UserRepository
     */
    public function getRepository(): UserRepository
    {
        return $this->entityRepository;
    }
}
