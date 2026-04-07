<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Event;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Event\PasswordValidationEvent;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(PasswordValidationEvent::class)]
class PasswordValidationEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new PasswordValidationEvent('my_password');

        $this->assertSame('my_password', $event->getPassword());
        $this->assertSame('spipu.user.password.validate', PasswordValidationEvent::EVENT_CODE);
    }
}
