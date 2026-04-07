<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UserBundle\Controller\AccountController;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(AccountController::class)]
class AccountDisableTest extends WebTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_SERVER['APP_ACCOUNT_CREATION']);
        unset($_SERVER['APP_ACCOUNT_RECOVERY']);
    }

    public function testCreateEnableRecoveryEnable(): void
    {
        $_SERVER['APP_ACCOUNT_CREATION'] = true;
        $_SERVER['APP_ACCOUNT_RECOVERY'] = true;

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Create an account")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Account Recovery")')->count());

        $client->request('GET', '/account/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/create-waiting');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/confirm/email/token');
        $this->assertTrue($client->getResponse()->isRedirect());

        $client->request('GET', '/account/recovery');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/recovery-waiting');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/new-password/email/token');
        $this->assertTrue($client->getResponse()->isRedirect());
    }

    public function testCreateEnableRecoveryDisable(): void
    {
        $_SERVER['APP_ACCOUNT_CREATION'] = true;
        $_SERVER['APP_ACCOUNT_RECOVERY'] = false;

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Create an account")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Account Recovery")')->count());

        $client->request('GET', '/account/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/create-waiting');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/confirm/email/token');
        $this->assertTrue($client->getResponse()->isRedirect());

        $client->request('GET', '/account/recovery');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/recovery-waiting');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/new-password/email/token');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testCreateDisableRecoveryEnable(): void
    {
        $_SERVER['APP_ACCOUNT_CREATION'] = false;
        $_SERVER['APP_ACCOUNT_RECOVERY'] = true;

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Create an account")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Account Recovery")')->count());

        $client->request('GET', '/account/create');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/create-waiting');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/confirm/email/token');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/recovery');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/recovery-waiting');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/account/new-password/email/token');
        $this->assertTrue($client->getResponse()->isRedirect());
    }
}
