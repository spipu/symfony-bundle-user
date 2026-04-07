<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UserBundle\Controller\AccountController;
use Spipu\UserBundle\Controller\ProfileController;
use Spipu\UserBundle\Controller\SecurityController;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Repository\UserRepository;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(AccountController::class)]
#[CoversClass(ProfileController::class)]
#[CoversClass(SecurityController::class)]
class AccountTest extends WebTestCase
{
    public const REGEX_URL = '/<a [^>]*>([^<]+)<\/a>/';

    public function test01NotExist(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login Submit
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'password_0'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Invalid Credentials
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Invalid credentials');
    }

    public function test02Creation(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // Login Page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Create an account")')->count());

        // Creation account page
        $crawler = $client->clickLink('Create an account');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Create an account")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());

        // Submit with missing password field
        $crawler = $client->submit(
            $crawler->selectButton('Create')->form(),
            [
                'generic[firstname]' => 'Test Firstname',
                'generic[lastname]'  => 'Test Lastname',
                'generic[email]'     => 'user@test.fr',
                'generic[username]'  => 'test_user',
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The password is required');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());

        // Submit with too short password
        $crawler = $client->submit(
            $crawler->selectButton('Create')->form(),
            [
                'generic[firstname]' => 'Test Firstname',
                'generic[lastname]'  => 'Test Lastname',
                'generic[email]'     => 'user@test.fr',
                'generic[username]'  => 'test_user',
                'generic[plainPassword][first]'  => 'short',
                'generic[plainPassword][second]' => 'short',
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The password is too short');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());

        // Needed to profile email
        $client->enableProfiler();

        // Submit with all fields
        $client->submit(
            $crawler->selectButton('Create')->form(),
            [
                'generic[firstname]' => 'Test Firstname',
                'generic[lastname]'  => 'Test Lastname',
                'generic[email]'     => 'user@test.fr',
                'generic[username]'  => 'test_user',
                'generic[plainPassword][first]'  => 'password_0',
                'generic[plainPassword][second]' => 'password_0',
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Get the send email
        $messageBody = $this->assertHasEmail('no-reply@mysite.fr', 'user@test.fr', 'Account Creation', 'user@test.fr');

        // Get the activation url
        $this->assertGreaterThan(0, preg_match(self::REGEX_URL, $messageBody, $match));
        $confirmUrlParts = explode('/', str_replace('http://localhost/', '', $match[1]));

        // Validate the url
        $token = array_pop($confirmUrlParts);
        $this->assertSame(['account', 'confirm', 'user@test.fr'], $confirmUrlParts);
        $confirmUrlParts[] = $token;

        // Confirmation page
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Account created")')->count());

        // Activation page
        $client->request('GET', '/' . implode('/', $confirmUrlParts));
        $this->assertTrue($client->getResponse()->isRedirect());

        // Redirect to Login Page with "You account has been activated"
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Your account has been activated');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());
    }

    public function test03BadPassword(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Submit with bad password
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'bad_password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Login page with "Invalid credentials"
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Invalid credentials');
    }

    public function test04GoodPassword(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("My Profile")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Submit good
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'password_0'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Redirect to Homage with logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("My Profile")')->count());

        // Logout action
        $client->clickLink("Log Out");
        $this->assertTrue($client->getResponse()->isRedirect());

        // Redirect to homepage with not logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("My Profile")')->count());
    }

    public function test05BadActivation(): void
    {
        $client = static::createClient();

        // Test bad email
        $client->request('GET', '/account/confirm/bad_email/bad_token');
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The email or the token is invalid');

        // Test bad token
        $client->request('GET', '/account/confirm/user@test.fr/bad_token');
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The email or the token is invalid');
    }

    public function test06RecoveryBadEmail(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // LogIn page
        $crawler = $client->clickLink("Log In");
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Account Recovery")')->count());

        // Account recovery page
        $crawler = $client->clickLink('Account Recovery');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Account Recovery")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Recover")')->count());

        $client->enableProfiler();

         // Submit Email
        $client->submit(
            $crawler->selectButton('Recover')->form(),
            ['generic[email]' => 'bad_email@test.fr']
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // No email
        $this->assertHasNoEmail();

        // Generic result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("An email has been sent")')->count());
    }

    public function test07RecoveryGoodEmail(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Account Recovery")')->count());

        // Account recovery page
        $crawler = $client->clickLink('Account Recovery');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Account Recovery")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Recover")')->count());

        $client->enableProfiler();

         // submit Email
        $client->submit(
            $crawler->selectButton('Recover')->form(),
            ['generic[email]' => 'user@test.fr']
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        $messageBody = $this->assertHasEmail('no-reply@mysite.fr', 'user@test.fr', 'Account Recovery');

        // Get the activation url
        $this->assertGreaterThan(0, preg_match(self::REGEX_URL, $messageBody, $match));
        $confirmUrlParts = explode('/', str_replace('http://localhost/', '', $match[1]));

        // Validate the url
        $token = array_pop($confirmUrlParts);
        $this->assertSame(['account', 'new-password', 'user@test.fr'], $confirmUrlParts);
        $confirmUrlParts[] = $token;

        // Generic result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("An email has been sent")')->count());

        // Recovery page
        $crawler = $client->request('GET', '/' . implode('/', $confirmUrlParts));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Account Recovery")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Submit bad password
        $crawler = $client->submit(
            $crawler->selectButton('Update')->form(),
            [
                'generic[plainPassword][first]'  => 'new_password',
                'generic[plainPassword][second]' => 'new_password2'
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasFormError($crawler, 'The values do not match');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Submit too short password
        $crawler = $client->submit(
            $crawler->selectButton('Update')->form(),
            [
                'generic[plainPassword][first]'  => 'short',
                'generic[plainPassword][second]' => 'short'
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The password is too short');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Submit good password
        $client->submit(
            $crawler->selectButton('Update')->form(),
            [
                'generic[plainPassword][first]'  => 'new_password',
                'generic[plainPassword][second]' => 'new_password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Your password has been changed
        $crawler = $client->followRedirect();
        $this->assertCrawlerHasAlert($crawler, 'Your password account has been changed');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Try the old password
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'password_0'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Invalid credentials
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Invalid credentials');

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Try the new password
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'new_password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page with logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());

        // Logout action
        $client->clickLink("Log Out");
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page not logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        /** @var SecurityController $controller */
        $controller = $client->getContainer()->get(SecurityController::class);
        $controller->logout();
    }

    public function test08BadRecovery(): void
    {
        $client = static::createClient();

        // bad email
        $client->request('GET', '/account/new-password/bad_email/bad_token');
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The email or the token is invalid');

        // bad token
        $client->request('GET', '/account/new-password/user@test.fr/bad_token');
        $this->assertTrue($client->getResponse()->isRedirect());

        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The email or the token is invalid');
    }

    public function test09CreationAlreadyTaken(): void
    {
        $client = static::createClient();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Create an account")')->count());

        // Creation account page
        $crawler = $client->clickLink('Create an account');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Create an account")')->count());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());

        // Submit with all fields
        $crawler = $client->submit(
            $crawler->selectButton('Create')->form(),
            [
                'generic[firstname]' => 'Test Firstname',
                'generic[lastname]'  => 'Test Lastname',
                'generic[email]'     => 'user@test.fr',
                'generic[username]'  => 'test_user',
                'generic[plainPassword][first]'  => 'password_0',
                'generic[plainPassword][second]' => 'password_0',
            ]
        );

        // Email and Username already taken
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasFormError($crawler, 'Email already taken');
        $this->assertCrawlerHasFormError($crawler, 'Username already taken');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());
    }

    public function test10EditProfile(): void
    {
        $client = static::createClient();

        // Home page not logged
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("My Profile")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'new_password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("My Profile")')->count());

        // View page
        $crawler = $client->clickLink("My Profile");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("My Profile")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Edit My Profile")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Change My Password")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertCrawlerHasFieldValue($crawler, 'firstname', 'Test Firstname');
        $this->assertCrawlerHasFieldValue($crawler, 'lastname', 'Test Lastname');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'user@test.fr');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test_user');

        // Edit page
        $crawler = $client->clickLink("Edit My Profile");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Save")')->count());

        // Needed to profile email
        $client->enableProfiler();

        // Submit new values with email change
        $client->submit(
            $crawler->filter('form#form_user_profile')->form(),
            [
                'generic[firstname]' => 'Change Firstname',
                'generic[lastname]'  => 'Change Lastname',
                'generic[email]'     => 'change@test.fr',
                'generic[username]'  => 'change_user',
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Verify notification email sent to old email
        $this->assertEmailCount(1);
        $notificationEmail = $this->getMailerMessage(0);
        $this->assertEmailHeaderSame($notificationEmail, 'To', 'user@test.fr');

        // View page - user stays logged in
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("My Profile")')->count());
        $this->assertCrawlerHasFieldValue($crawler, 'firstname', 'Change Firstname');
        $this->assertCrawlerHasFieldValue($crawler, 'lastname', 'Change Lastname');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'change@test.fr');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'change_user');

        // Edit page - restore values
        $crawler = $client->clickLink("Edit My Profile");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Save")')->count());

        // Restore values
        $client->submit(
            $crawler->filter('form#form_user_profile')->form(),
            [
                'generic[firstname]' => 'Test Firstname',
                'generic[lastname]'  => 'Test Lastname',
                'generic[email]'     => 'user@test.fr',
                'generic[username]'  => 'test_user',
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // View page
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("My Profile")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been saved');
        $this->assertCrawlerHasFieldValue($crawler, 'firstname', 'Test Firstname');
        $this->assertCrawlerHasFieldValue($crawler, 'lastname', 'Test Lastname');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'user@test.fr');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test_user');

        // Password page
        $crawler = $client->clickLink("Change My Password");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Bad old Password
        $crawler = $client->submit(
            $crawler->filter('form#form_user_password')->form(),
            [
                'generic[oldPassword]'           => 'bad_password_0',
                'generic[plainPassword][first]'  => 'password_0',
                'generic[plainPassword][second]' => 'password_0',
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Your old password is wrong');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Too short new password
        $crawler = $client->submit(
            $crawler->filter('form#form_user_password')->form(),
            [
                'generic[oldPassword]'           => 'new_password',
                'generic[plainPassword][first]'  => 'short',
                'generic[plainPassword][second]' => 'short',
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The password is too short');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Good old Password
        $client->submit(
            $crawler->filter('form#form_user_password')->form(),
            [
                'generic[oldPassword]'           => 'new_password',
                'generic[plainPassword][first]'  => 'password_0',
                'generic[plainPassword][second]' => 'password_0',
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // View page
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The item has been saved');

        // Logout action
        $client->clickLink("Log Out");
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page not logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login with new password
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'password_0'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Home page logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("My Profile")')->count());
    }

    public function test11RecoveryDisabledAccount(): void
    {
        $client = static::createClient();

        // Disable the user
        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var UserInterface $user */
        $user = $userRepository->findOneBy(['email' => 'user@test.fr']);
        $user->setActive(false);
        $entityManager->flush();

        // Home page
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Account Recovery")')->count());

        // Account recovery page
        $crawler = $client->clickLink('Account Recovery');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Recover")')->count());

        $client->enableProfiler();

        // Submit Email
        $client->submit(
            $crawler->selectButton('Recover')->form(),
            ['generic[email]' => 'user@test.fr']
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // No email sent
        $this->assertHasNoEmail();

        // Generic result (same as bad email)
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("An email has been sent")')->count());

        // Re-enable the user (re-fetch: entity is detached after HTTP requests)
        $user = $userRepository->findOneBy(['email' => 'user@test.fr']);
        $user->setActive(true);
        $entityManager->flush();
    }

    public function test12LoginDisabledShowsGenericError(): void
    {
        $client = static::createClient();

        // Disable the user
        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        /** @var UserInterface $user */
        $user = $userRepository->findOneBy(['email' => 'user@test.fr']);
        $user->setActive(false);
        $entityManager->flush();

        // Login page
        $crawler = $client->request('GET', '/login');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Submit with correct credentials
        $client->submit(
            $crawler->selectButton('Log In')->form(),
            [
                '_username' => 'test_user',
                '_password' => 'password_0'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Same generic error as bad password
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Invalid credentials');

        // Re-enable the user (re-fetch: entity is detached after HTTP requests)
        $user = $userRepository->findOneBy(['email' => 'user@test.fr']);
        $user->setActive(true);
        $entityManager->flush();
    }
}
