<?php
namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Tests\Unit\Service\RoleDefinitionUiTest;
use Spipu\UserBundle\Service\RoleDefinition;

class RoleDefinitionTest extends TestCase
{

    public function testService()
    {
        $items = RoleDefinitionUiTest::loadRoles($this, new RoleDefinition());

        $this->assertEquals(4, count($items));

        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_USER_SHOW', $items);
        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_USER_EDIT', $items);
        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_USER_DELETE', $items);
        $this->assertArrayHasKey('ROLE_ADMIN_MANAGE_USER', $items);

        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_USER_SHOW']->getType());
        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_USER_EDIT']->getType());
        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_USER_DELETE']->getType());
        $this->assertEquals(Item::TYPE_ROLE, $items['ROLE_ADMIN_MANAGE_USER']->getType());

        Item::resetAll();
    }
}
