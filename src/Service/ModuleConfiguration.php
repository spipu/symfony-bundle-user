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
    private string $entityName;
    private string $entityClassName;
    private bool $allowAccountCreation;
    private bool $allowPasswordRecovery;

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

    public function hasAllowAccountCreation(): bool
    {
        return $this->allowAccountCreation;
    }

    public function hasAllowPasswordRecovery(): bool
    {
        return $this->allowPasswordRecovery;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    public function getNewEntity(): UserInterface
    {
        $className = $this->getEntityClassName();

        return new $className();
    }
}
