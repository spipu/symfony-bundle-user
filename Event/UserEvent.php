<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Event;

use Spipu\UserBundle\Entity\GenericUser;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Grid Event
 */
class UserEvent extends Event
{
    const PREFIX_NAME = 'spipu.user.action.';

    /**
     * @var GenericUser
     */
    private $user;

    /**
     * @var string
     */
    private $action;

    /**
     * GridEvent constructor.
     * @param GenericUser $user
     * @param string $action
     */
    public function __construct(GenericUser $user, string $action)
    {
        $this->user = $user;
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getEventCode(): string
    {
        return static::PREFIX_NAME . $this->action;
    }

    /**
     * @return GenericUser
     */
    public function getUser(): GenericUser
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }
}
