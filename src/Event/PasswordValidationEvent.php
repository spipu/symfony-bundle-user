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

namespace Spipu\UserBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class PasswordValidationEvent extends Event
{
    public const EVENT_CODE = 'spipu.user.password.validate';

    private string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
