<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionList;
use Spipu\UserBundle\Service\RoleDefinition;
use Spipu\UserBundle\Service\RoleService;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(RoleService::class)]
class RoleServiceTest extends TestCase
{
    private function getRoleService(): RoleService
    {
        Item::resetAll();

        $roleDefinitionList = new RoleDefinitionList([new RoleDefinition()]);

        return new RoleService($roleDefinitionList, 'admin');
    }

    public function testSortRoles(): void
    {
        $service = $this->getRoleService();

        $itemA = Item::load('ROLE_A');
        $itemA->setWeight(30);
        $itemB = Item::load('ROLE_B');
        $itemB->setWeight(10);
        $itemC = Item::load('ROLE_C');
        $itemC->setWeight(20);

        $items = ['a' => $itemA, 'b' => $itemB, 'c' => $itemC];
        $service->sortRoles($items);

        $keys = array_keys($items);
        $this->assertSame(['b', 'c', 'a'], $keys);

        Item::resetAll();
    }

    public function testGetProfiles(): void
    {
        $service = $this->getRoleService();

        $profiles = $service->getProfiles();

        $this->assertIsArray($profiles);
        foreach ($profiles as $profile) {
            $this->assertSame(Item::TYPE_PROFILE, $profile->getType());
        }

        Item::resetAll();
    }

    public function testGetRoles(): void
    {
        $service = $this->getRoleService();

        $roles = $service->getRoles();

        $this->assertIsArray($roles);
        $this->assertNotEmpty($roles);
        foreach ($roles as $role) {
            $this->assertSame(Item::TYPE_ROLE, $role->getType());
        }

        Item::resetAll();
    }

    public function testGetRolesRemovesChildren(): void
    {
        $service = $this->getRoleService();

        $roles = $service->getRoles();

        // A parent role's children should not appear as top-level roles
        foreach ($roles as $role) {
            foreach ($role->getChildren() as $child) {
                if ($child->getType() === Item::TYPE_ROLE) {
                    $this->assertArrayNotHasKey($child->getCode(), $roles);
                }
            }
        }

        Item::resetAll();
    }

    public function testGetRoleChildren(): void
    {
        $service = $this->getRoleService();

        $roles = $service->getRoles();
        $parentRole = null;
        foreach ($roles as $role) {
            if (count($role->getChildren()) > 0) {
                $parentRole = $role;
                break;
            }
        }

        if ($parentRole !== null) {
            $children = $service->getRoleChildren($parentRole);
            $this->assertIsArray($children);
            foreach ($children as $child) {
                $this->assertSame(Item::TYPE_ROLE, $child->getType());
            }
        }

        Item::resetAll();
    }

    public function testGetProfileRoleList(): void
    {
        $service = $this->getRoleService();

        $profile = Item::load('PROFILE_TEST');
        $profile->setType(Item::TYPE_PROFILE)->setPurpose('admin')->setWeight(10);
        $profile->addChild('ROLE_ADMIN_MANAGE_USER_SHOW');

        $roleList = $service->getProfileRoleList($profile);
        $this->assertIsArray($roleList);
        $this->assertNotEmpty($roleList);
        $this->assertContains('ROLE_ADMIN_MANAGE_USER_SHOW', $roleList);

        Item::resetAll();
    }

    public function testGetProfileRoleListWithNonProfile(): void
    {
        $service = $this->getRoleService();

        $roles = $service->getRoles();
        $role = reset($roles);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role is not a profile');
        $service->getProfileRoleList($role);

        Item::resetAll();
    }

    public function testHasRole(): void
    {
        $service = $this->getRoleService();

        $item = Item::load('ROLE_TEST_HAS');

        $this->assertTrue($service->hasRole(['ROLE_TEST_HAS', 'ROLE_OTHER'], $item));
        $this->assertFalse($service->hasRole(['ROLE_OTHER'], $item));
        $this->assertFalse($service->hasRole([], $item));

        Item::resetAll();
    }

    public function testValidateRoles(): void
    {
        $service = $this->getRoleService();

        $roles = $service->getRoles();
        $validCodes = array_keys($roles);

        $this->assertTrue($service->validateRoles($validCodes));
        $this->assertTrue($service->validateRoles([]));
        $this->assertFalse($service->validateRoles(['ROLE_DOES_NOT_EXIST_AT_ALL']));

        Item::resetAll();
    }
}
