<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Service\MailConfiguration;
use Spipu\UserBundle\Service\MailConfigurationInterface;

class MailConfigurationTest extends TestCase
{
    public static function getService(TestCase $testCase): MailConfigurationInterface
    {
        $mailConfiguration = new MailConfiguration();

        return $mailConfiguration;
    }

    public function testService(): void
    {
        $mailConfiguration = self::getService($this);

        $this->assertSame('no-reply@mysite.fr', $mailConfiguration->getEmailFrom());
    }
}
