<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

use Spipu\UserBundle\Entity\UserInterface;

interface ModuleConfigurationInterface
{
    /**
     * @return bool
     */
    public function hasAllowAccountCreation(): bool;
    /**
     * @return bool
     */
    public function hasAllowPasswordRecovery(): bool;

    /**
     * @return string
     */
    public function getEntityName(): string;

    /**
     * @return string
     */
    public function getEntityClassName(): string;

    /**
     * @return UserInterface
     */
    public function getNewEntity(): UserInterface;
}
