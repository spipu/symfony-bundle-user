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

use Spipu\ConfigurationBundle\Service\ConfigurationManager;

class UserConfiguration
{
    private ConfigurationManager $configurationManager;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function hasSecurityLockEnabled(): bool
    {
        return ((int) $this->configurationManager->get('user.security.lock_enabled') == 1);
    }

    public function getSecurityLockMaxAttempts(): int
    {
        $value = (int) $this->configurationManager->get('user.security.lock_max_attempts');

        if ($value < 1) {
            $value = 1;
        }

        return $value;
    }
}
