<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UserBundle\Controller\SecurityController;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie as BrowserCookie;
use Symfony\Component\HttpFoundation\Cookie;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(SecurityController::class)]
class RememberMeTest extends WebTestCase
{
    private const COOKIE_NAME = 'symfony_dev_main_remember';

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_SERVER['APP_REMEMBER_ME']);
    }

    public function testCheckboxDisplayed(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = true;

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('input#remember_me')->count());
    }

    public function testCheckboxHidden(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = false;

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('input#remember_me')->count());
    }

    public function testCookieCreated(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = true;

        $client = static::createClient();

        $cookie = $this->login($client, true);
        $this->assertNotNull($cookie);
        $this->assertNotNull($cookie->getValue());
        $this->assertTrue($cookie->isHttpOnly());
    }

    public function testCookieNotCreated(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = true;

        $client = static::createClient();

        $cookie = $this->login($client, false);
        $this->assertTrue($cookie === null || $cookie->getValue() === null);
    }

    public function testCookieForgedNotAllowed(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = false;

        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('input#remember_me')->count());

        // The checkbox is hidden, but the field is forged in the request.
        $form = $crawler->selectButton('Log In')->form();
        $values = $form->getPhpValues();
        $values['_username'] = 'admin';
        $values['_password'] = 'password';
        $values['_remember_me'] = 'on';

        $client->request('POST', $form->getUri(), $values);
        $this->assertTrue($client->getResponse()->isRedirect());

        $cookie = $this->getResponseCookie($client);
        $this->assertTrue($cookie === null || $cookie->getValue() === null);
    }

    public function testReconnectWithCookie(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = true;

        $client = static::createClient();
        $this->login($client, true);
        $this->keepOnlyRememberMeCookie($client);

        $crawler = $client->request('GET', '/my-profile/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
    }

    public function testCookieInvalidatedByPasswordChange(): void
    {
        $_SERVER['APP_REMEMBER_ME'] = true;

        $client = static::createClient();
        $this->login($client, true);
        $this->keepOnlyRememberMeCookie($client);

        $originalPassword = $this->setAdminPassword('new_encoded_password');

        try {
            $client->request('GET', '/my-profile/');
            $this->assertTrue($client->getResponse()->isRedirect());
            $this->assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
        } finally {
            // The database is shared by all the functional tests.
            $this->setAdminPassword($originalPassword);
        }
    }

    private function setAdminPassword(string $password): string
    {
        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var UserInterface $user */
        $user = $userRepository->findOneBy(['username' => 'admin']);

        $previousPassword = (string) $user->getPassword();
        $user->setPassword($password);
        $entityManager->flush();

        return $previousPassword;
    }

    private function keepOnlyRememberMeCookie(KernelBrowser $client): void
    {
        $cookieJar = $client->getCookieJar();
        $cookie = $cookieJar->get(self::COOKIE_NAME);
        $this->assertInstanceOf(BrowserCookie::class, $cookie);

        // The session is lost, only the remember me cookie is kept.
        $cookieJar->clear();
        $cookieJar->set($cookie);
    }

    private function login(KernelBrowser $client, bool $rememberMe): ?Cookie
    {
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Log In')->form();
        $form['_username'] = 'admin';
        $form['_password'] = 'password';
        if ($rememberMe) {
            $form['_remember_me']->tick();
        }

        $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect());

        return $this->getResponseCookie($client);
    }

    private function getResponseCookie(KernelBrowser $client): ?Cookie
    {
        foreach ($client->getResponse()->headers->getCookies() as $cookie) {
            if ($cookie->getName() === self::COOKIE_NAME) {
                return $cookie;
            }
        }

        return null;
    }
}
