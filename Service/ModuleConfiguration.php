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

namespace Spipu\UserBundle\Service;

use Spipu\UserBundle\Entity\UserInterface;

class ModuleConfiguration implements ModuleConfigurationInterface
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
     * @param bool $allowAccountCreation
     * @param bool $allowPasswordRecovery
     */
    public function __construct(
        string $entityName,
        string $entityClassName,
        bool $allowAccountCreation,
        bool $allowPasswordRecovery
    ) {
        $this->entityName = $entityName;
        $this->entityClassName = $entityClassName;
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
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return UserInterface
     */
    public function getNewEntity(): UserInterface
    {
        $className = $this->getEntityClassName();

        return new $className();
    }
}
