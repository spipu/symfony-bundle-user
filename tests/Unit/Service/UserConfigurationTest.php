<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\ConfigurationBundle\Tests\SpipuConfigurationMock;
use Spipu\UserBundle\Service\UserConfiguration;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(UserConfiguration::class)]
class UserConfigurationTest extends TestCase
{
    private static function getDefaultValues(): array
    {
        return [
            'user.security.lock_enabled'        => 1,
            'user.security.lock_max_attempts'    => 10,
            'user.security.token_expiration'     => 12,
            'user.security.password_min_length'  => 10,
        ];
    }

    public static function getService(TestCase $testCase, array $values = []): UserConfiguration
    {
        $values = array_merge(self::getDefaultValues(), $values);

        $manager = SpipuConfigurationMock::getManager($testCase, null, $values);

        return new UserConfiguration($manager);
    }

    public function testDefaultValues(): void
    {
        $service = self::getService($this);

        $this->assertSame(true, $service->hasSecurityLockEnabled());
        $this->assertSame(10, $service->getSecurityLockMaxAttempts());
        $this->assertSame(12, $service->getSecurityTokenExpiration());
        $this->assertSame(10, $service->getSecurityPasswordMinLength());
    }

    public function testLockEnabled(): void
    {
        $service = self::getService($this, ['user.security.lock_enabled' => 1]);
        $this->assertSame(true, $service->hasSecurityLockEnabled());

        $service = self::getService($this, ['user.security.lock_enabled' => 0]);
        $this->assertSame(false, $service->hasSecurityLockEnabled());
    }

    public function testLockMaxAttempts(): void
    {
        $service = self::getService($this, ['user.security.lock_max_attempts' => 5]);
        $this->assertSame(5, $service->getSecurityLockMaxAttempts());

        $service = self::getService($this, ['user.security.lock_max_attempts' => 0]);
        $this->assertSame(1, $service->getSecurityLockMaxAttempts());

        $service = self::getService($this, ['user.security.lock_max_attempts' => -10]);
        $this->assertSame(1, $service->getSecurityLockMaxAttempts());
    }

    public function testPasswordMinLength(): void
    {
        $service = self::getService($this, ['user.security.password_min_length' => 12]);
        $this->assertSame(12, $service->getSecurityPasswordMinLength());

        $service = self::getService($this, ['user.security.password_min_length' => 0]);
        $this->assertSame(8, $service->getSecurityPasswordMinLength());

        $service = self::getService($this, ['user.security.password_min_length' => -5]);
        $this->assertSame(8, $service->getSecurityPasswordMinLength());

        $service = self::getService($this, ['user.security.password_min_length' => 5]);
        $this->assertSame(8, $service->getSecurityPasswordMinLength());
    }

    public function testTokenExpiration(): void
    {
        $service = self::getService($this, ['user.security.token_expiration' => 24]);
        $this->assertSame(24, $service->getSecurityTokenExpiration());

        $service = self::getService($this, ['user.security.token_expiration' => 0]);
        $this->assertSame(1, $service->getSecurityTokenExpiration());

        $service = self::getService($this, ['user.security.token_expiration' => -5]);
        $this->assertSame(1, $service->getSecurityTokenExpiration());
    }
}
