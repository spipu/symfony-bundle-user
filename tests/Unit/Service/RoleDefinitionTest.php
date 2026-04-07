<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Tests\Unit\Service\RoleDefinitionUiTest;
use Spipu\UserBundle\Service\RoleDefinition;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(RoleDefinition::class)]
class RoleDefinitionTest extends TestCase
{

    public function testService(): void
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
