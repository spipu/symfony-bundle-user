<?php
namespace Spipu\UserBundle\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Event\UserEvent;

class UserEventTest extends TestCase
{
    public function testEvent()
    {
        $user = new GenericUser();

        $event = new UserEvent($user, 'mock');

        $this->assertSame($user, $event->getUser());
        $this->assertSame('mock', $event->getAction());
        $this->assertSame('spipu.user.action.mock', $event->getEventCode());
    }
}
