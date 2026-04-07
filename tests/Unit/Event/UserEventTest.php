<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Event\UserEvent;
use Spipu\UserBundle\Tests\GenericUser;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(UserEvent::class)]
class UserEventTest extends TestCase
{
    public function testEvent(): void
    {
        $user = new GenericUser();

        $event = new UserEvent($user, 'mock');

        $this->assertSame($user, $event->getUser());
        $this->assertSame('mock', $event->getAction());
        $this->assertSame('spipu.user.action.mock', $event->getEventCode());
    }
}
