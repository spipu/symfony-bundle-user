<?php /** @noinspection CssInvalidPseudoSelector */

namespace Spipu\UserBundle\Tests\Functional;

use Spipu\CoreBundle\Tests\WebTestCase;
use Spipu\UiBundle\Tests\UiWebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class AdminUserTest extends WebTestCase
{
    use UiWebTestCaseTrait;

    public function testBadAcl()
    {
        $client = static::createClient();

        // Home page not logged
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Log In")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Log Out")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Users")')->count());

        // Login page
        $crawler = $client->clickLink("Log In");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Log In")')->count());

        // Login
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
        $this->assertEquals(0, $crawler->filter('a:contains("Users")')->count());
    }

    public function testAdminCrud()
    {
        $client = static::createClient();

        $this->adminLogin($client, 'Users');

        // Users List
        $crawler = $client->clickLink('Users');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSame('2002 items found', $crawler->filter('span[data-grid-role=total-rows]')->text());

        $this->assertGreaterThan(0, $crawler->filter('button:contains("Advanced Search")')->count());

        // Users List with filter
        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[username]' => 'test2']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("No item found")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Create")')->count());

        // Reset filter
        $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[username]' => '']);

        // Create
        $crawler = $client->clickLink('Create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Create")')->count());

        // Needed to profile email
        $client->enableProfiler();

        // Submit new values
        $client->submit(
            $crawler->filter('form#form_user_admin')->form(),
            [
                'generic[firstname]' => 'Test2 Firstname',
                'generic[lastname]'  => 'Test2 Lastname',
                'generic[email]'     => 'user2@test.fr',
                'generic[username]'  => 'test2_user',
                'generic[active]'    => 1,
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Get the email
        $this->assertHasEmail('no-reply@mysite.fr', 'user2@test.fr', 'Account Recovery', 'user2@test.fr');

        // Show user page - Enabled
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been saved');
        $this->assertCrawlerHasFieldValue($crawler, 'firstname', 'Test2 Firstname');
        $this->assertCrawlerHasFieldValue($crawler, 'lastname', 'Test2 Lastname');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'user2@test.fr');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test2_user');
        $this->assertCrawlerHasFieldValue($crawler, 'active', 'Yes');
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Disable")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Enable")')->count());

        // Disable the user
        $client->clickLink('Disable');
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show user page - Disabled
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been disabled');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test2_user');
        $this->assertCrawlerHasFieldValue($crawler, 'active', 'No');
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Enable")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Disable")')->count());

        // Enable the user
        $client->clickLink('Enable');
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show user page - Enabled
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been enabled');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test2_user');
        $this->assertCrawlerHasFieldValue($crawler, 'active', 'Yes');
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Disable")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Enable")')->count());
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Password")')->count());

        // Needed to profile email
        $client->enableProfiler();

        // Reset the password
        $client->clickLink('Password');
        $this->assertTrue($client->getResponse()->isRedirect());

        // Get the email
        $this->assertHasEmail('no-reply@mysite.fr', 'user2@test.fr', 'Account Recovery', 'user2@test.fr');

        // Show user page - Reset
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test2_user');
        $this->assertCrawlerHasAlert($crawler, 'A password recovery email has been sent');

        // Users List page
        $crawler = $client->clickLink('Users');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Advanced Search")')->count());

        // Users List with filter
        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[username]' => 'test2']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("1 item found")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Show")')->count());

        // Show page
        $crawler = $client->clickLink('Show');
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test2_user');
        $this->assertGreaterThan(0, $crawler->filter('a:contains("Edit")')->count());

        // Edit page
        $crawler = $client->clickLink('Edit');

        // Submit new values
        $client->submit(
            $crawler->filter('form#form_user_admin')->form(),
            [
                'generic[firstname]' => 'Test3 Firstname',
                'generic[lastname]'  => 'Test3 Lastname',
                'generic[email]'     => 'user3@test.fr',
                'generic[username]'  => 'test3_user',
                'generic[active]'    => 0
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show page
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been saved');
        $this->assertCrawlerHasFieldValue($crawler, 'firstname', 'Test3 Firstname');
        $this->assertCrawlerHasFieldValue($crawler, 'lastname', 'Test3 Lastname');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'user3@test.fr');
        $this->assertCrawlerHasFieldValue($crawler, 'username', 'test3_user');
        $this->assertCrawlerHasFieldValue($crawler, 'active', 'No');
        $this->assertGreaterThan(0, $crawler->filter('button:contains("Delete")')->count());

        // Submit ACL
        $client->submit(
            $crawler->filter('button:contains("Save ACL")')->form(),
            [
                'acl[1]' => 'ROLE_ADMIN',
            ]
        );
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show page
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Show User")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been updated');
        $this->assertCrawlerHasFieldValue($crawler, 'email', 'user3@test.fr');

        // Delete the account
        $client->submit($crawler->filter('button:contains("Delete")')->form());
        $this->assertTrue($client->getResponse()->isRedirect());

        // Return to the list (it must keep the filters)
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('h1:contains("Users")')->count());
        $this->assertCrawlerHasAlert($crawler, 'The item has been deleted');
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("No item found")')->count());

        // Reset the filter
        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[username]' => '']);

        // Filter on ids
        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[id][from]' => '11', 'fl[id][to]' => '52', 'fl[is_active]' => '0']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("42 items found")')->count());

        // Reset the filter
        $crawler = $client->submit($crawler->selectButton('Advanced Search')->form(), ['fl[id][from]' => '', 'fl[id][to]' => '', 'fl[is_active]' => '']);

        // The ids to disable
        $userIds = [2, 3];

        // The users 2 and 3 must be disabled
        foreach ($userIds as $userId) {
            $this->assertEquals(0, $crawler->filter('tr[data-grid-row-id=' . $userId . '] td[data-grid-field-name=is_active]:contains("Yes")')->count());
        }

        // The mass action "Enable" must exist
        $linkAction = $crawler->filter('span[data-grid-role="action"]:contains("Enable")');
        $this->assertEquals(1, $linkAction->count());
        $linkUrl = $linkAction->first()->attr('data-grid-href');

        // Post the mass action "Enable"
        $client->request('POST', $linkUrl, ['selected' => json_encode($userIds)]);
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show grid result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('div[role="alert"]:contains("' . count($userIds) . ' items have been enabled")')->count());

        // The users must be enabled
        foreach ($userIds as $userId) {
            $this->assertEquals(1, $crawler->filter('tr[data-grid-row-id=' . $userId . '] td[data-grid-field-name=is_active]:contains("Yes")')->count());
        }

        // The mass action "Disable" must exist
        $linkAction = $crawler->filter('span[data-grid-role="action"]:contains("Disable")');
        $this->assertEquals(1, $linkAction->count());
        $linkUrl = $linkAction->first()->attr('data-grid-href');

        // Post the mass action "Disable"
        $client->request('POST', $linkUrl, ['selected' => json_encode($userIds)]);
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show grid result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('div[role="alert"]:contains("' . count($userIds) . ' items have been disabled")')->count());

        // The users must be disabled
        foreach ($userIds as $userId) {
            $this->assertEquals(0, $crawler->filter('tr[data-grid-row-id=' . $userId . '] td[data-grid-field-name=is_active]:contains("Yes")')->count());
        }

        // Post the mass action "Disable" on the same users
        $client->request('POST', $linkUrl, ['selected' => json_encode($userIds)]);
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show grid result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('div[role="alert"]:contains("0 items have been disabled")')->count());

        // Post the mass action "Disable" on our user
        $client->request('POST', $linkUrl, ['selected' => json_encode([1])]);
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show grid result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('div[role="alert"]:contains("You can not disable yourself!")')->count());
        $this->assertEquals(1, $crawler->filter('div[role="alert"]:contains("0 items have been disabled")')->count());

        // Post the mass action "Disable" on empty list
        $client->request('POST', $linkUrl, ['selected' => json_encode([])]);
        $this->assertTrue($client->getResponse()->isRedirect());

        // Show grid result
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('div[role="alert"]:contains("You must select at least one item")')->count());

        // Users List with quick search
        $crawler = $client->submit($crawler->selectButton('Search')->form(), ['qs[field]' => 'email', 'qs[value]' => 'user_42@']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('span[data-grid-role=total-rows]:contains("1 item found")')->count());

        // Reset Users List with quick search
        $crawler = $client->submit($crawler->selectButton('Search')->form(), ['qs[field]' => 'id', 'qs[value]' => '']);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSame('2002 items found', $crawler->filter('span[data-grid-role=total-rows]')->text());
    }

    public function testAdminGridConfig()
    {
        $client = static::createClient();

        $this->adminLogin($client, 'Users');

        // Users List - Only default display is available
        $crawler = $client->clickLink('Users');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSame('2002 items found', $crawler->filter('span[data-grid-role=total-rows]')->text());
        $options = $this->getGridConfigDisplayList($crawler);
        $expected = ['default' => ['id' => 1, 'selected' => true]];
        $this->assertSame($expected, $options);

        // Create new display - Name is missing
        $client->clickLink('Create a new display');
        $crawler = $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('button[data-grid-role=config-create-submit]')->form(),
            ['cf[action]' => 'create'],
            'Name is missing',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true]]
        );

        // Create new display - Name is invalid
        $client->clickLink('Create a new display');
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('button[data-grid-role=config-create-submit]')->form(),
            ['cf[action]' => 'create', 'cf[name]' => '<b> </b>'],
            'Name is invalid',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true]]
        );

        // Create new display - good name
        $client->clickLink('Create a new display');
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('button[data-grid-role=config-create-submit]')->form(),
            ['cf[action]' => 'create', 'cf[name]' => 'My display'],
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Select display - default
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('form[data-grid-role=config-select-form]')->form(),
            ['cf[action]' => 'select', 'cf[id]' => 1],
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true], 'my display' => ['id' => 2, 'selected' => false]]
        );

        // Select display - id is invalid
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-select-form]')->form(),
            ['cf[action]' => 'select', 'cf[id]' => 'foo'],
            'Id is invalid',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true], 'my display' => ['id' => 2, 'selected' => false]]
        );

        // Select display - id is unknown
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-select-form]')->form(),
            ['cf[action]' => 'select', 'cf[id]' => '3'],
            'Id is unknown',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true], 'my display' => ['id' => 2, 'selected' => false]]
        );

        // Select display - New display
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('form[data-grid-role=config-select-form]')->form(),
            ['cf[action]' => 'select', 'cf[id]' => 2],
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - column is not a array
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns]' => 'foo',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - column name is not a string
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0][0]' => 1,
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - column is unknown
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'foo',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - column is empty
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => '----',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters][is_active]' => '0',
            ],
            'you must at least display one column',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - sort is not an array
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort]' => 'foo',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - sort.column is missing
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort][order]'  => 'asc',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - sort.order is missing
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort][column]' => 'username',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - sort.column is unknown
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort][column]' => 'foo',
                'cf[sort][order]'  => 'asc',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - sort.order is invalid
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'foo',
                'cf[filters][is_active]' => '0',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - filters is not an array
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters]' => 'foo',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - bad data - filters.column name is unknown
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters][foo]' => 'bar',
            ],
            'bad data',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - ok but with no sort and no filter
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
            ],
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - ok but without no sort
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]' => 'update',
                'cf[id]'     => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[filters][is_active]' => '0',
            ],
            '2000 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Configure new display - OK
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            [
                'cf[action]'     => 'update',
                'cf[id]'         => '2',
                'cf[columns][0]' => 'id',
                'cf[columns][1]' => 'username',
                'cf[columns][2]' => 'email',
                'cf[columns][3]' => '----',
                'cf[columns][4]' => 'middle_name',
                'cf[columns][5]' => 'is_active',
                'cf[columns][6]' => 'nb_login',
                'cf[columns][7]' => 'updated_at',
                'cf[sort][column]' => 'username',
                'cf[sort][order]'  => 'desc',
                'cf[filters][is_active]' => '0',
            ],
            '2000 items found',
            ['default' => ['id' => 1, 'selected' => false], 'my display' => ['id' => 2, 'selected' => true]]
        );

        // Delete display - default display not allowed
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            ['cf[action]' => 'delete', 'cf[id]' => '1'],
            'You can not delete the default display',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true], 'my display' => ['id' => 2, 'selected' => false]]
        );

        // Delete display - New display
        $this->submitGridConfigWithGoodValues(
            $client,
            $crawler->filter('form[data-grid-role=config-form]')->form(),
            ['cf[action]' => 'delete', 'cf[id]' => '2'],
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true]]
        );

        // Unknown action
        $this->submitGridConfigWithWrongValues(
            $client,
            $crawler->filter('button[data-grid-role=config-create-submit]')->form(),
            ['cf[action]' => 'foo'],
            'Unknown grid config action: foo',
            '2002 items found',
            ['default' => ['id' => 1, 'selected' => true]]
        );
    }

    public function testBadAccess()
    {
        $client = static::createClient();

        $this->adminLogin($client, 'Users');

        $client->request('GET', '/user/show/999999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/user/delete/999999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('DELETE', '/user/delete/1');
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'You can not delete yourself');

        $client->request('DELETE', '/user/delete/2');
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'Invalid Token');

        $client->request('GET', '/user/enable/999999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/user/enable/1');
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'You can not enable yourself');

        $client->request('GET', '/user/disable/999999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/user/disable/1');
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertCrawlerHasAlert($crawler, 'You can not disable yourself');

        $client->request('GET', '/user/reset/999999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $client->request('GET', '/user/edit/999999999');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
