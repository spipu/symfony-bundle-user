<?php
namespace Spipu\UserBundle\Tests\Functional;

use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UserBundle\Controller\SecurityController;

class AccountTest extends WebTestCase
{
    const REGEX_URL = '/<a [^>]*>([^<]+)<\/a>/';

    public function test01NotExist()
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
                '_password' => 'password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Bad Credentials
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Bad credentials');
    }

    public function test02Creation()
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
                'generic[plainPassword][first]'  => 'password',
                'generic[plainPassword][second]' => 'password',
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Get the sent email
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

    public function test03BadPassword()
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

        // Login page with "Bad credentials"
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The presented password is invalid.');
    }

    public function test04GoodPassword()
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
                '_password' => 'password'
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

        // Redirect to to homepage with not logged
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("My Profile")')->count());
    }

    public function test05BadActivation()
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

    public function test06RecoveryBadEmail()
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

    public function test07RecoveryGoodEmail()
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
        $this->assertCrawlerHasFormError($crawler, 'This value is not valid');
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

        // You password has been changed
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
                '_password' => 'password'
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // bad credentials
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'The presented password is invalid.');

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

    public function test08BadRecovery()
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

    public function test09CreationAlreadyTaken()
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
                'generic[plainPassword][first]'  => 'password',
                'generic[plainPassword][second]' => 'password',
            ]
        );

        // Email and Username already taken
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasFormError($crawler, 'Email already taken');
        $this->assertCrawlerHasFormError($crawler, 'Username already taken');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());
    }

    public function test10EditProfile()
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

        // Submit new values
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

        // View page
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("My Profile")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been saved');
        $this->assertCrawlerHasFieldValue($crawler, 'firstname', 'Change Firstname');
        $this->assertCrawlerHasFieldValue($crawler, 'lastname', 'Change Lastname');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'change@test.fr');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'change_user');

        // Edit page
        $crawler = $client->clickLink("Edit My Profile");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Save")')->count());

        // Restore Values
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
                'generic[oldPassword]'           => 'bad_password',
                'generic[plainPassword][first]'  => 'password',
                'generic[plainPassword][second]' => 'password',
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Your old password is wrong');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Update")')->count());

        // Good old Password
        $client->submit(
            $crawler->filter('form#form_user_password')->form(),
            [
                'generic[oldPassword]'           => 'new_password',
                'generic[plainPassword][first]'  => 'password',
                'generic[plainPassword][second]' => 'password',
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
                '_password' => 'password'
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
}
