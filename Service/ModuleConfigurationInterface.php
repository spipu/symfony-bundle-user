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

interface ModuleConfigurationInterface
{
    public function hasAllowAccountCreation(): bool;

    public function hasAllowPasswordRecovery(): bool;

    public function getEntityName(): string;

    public function getEntityClassName(): string;

    public function getNewEntity(): UserInterface;
}
