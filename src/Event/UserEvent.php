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

use Spipu\UserBundle\Entity\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    public const PREFIX_NAME = 'spipu.user.action.';

    private UserInterface $user;
    private string $action;

    public function __construct(UserInterface $user, string $action)
    {
        $this->user = $user;
        $this->action = $action;
    }

    public function getEventCode(): string
    {
        return static::PREFIX_NAME . $this->action;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
