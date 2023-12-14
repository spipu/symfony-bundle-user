<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UserBundle\Service;

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionList;

class RoleService
{
    private RoleDefinitionList $roleDefinitionList;
    private string $purpose;

    public function __construct(
        RoleDefinitionList $roleDefinitionList,
        string $purpose = 'admin'
    ) {
        $this->roleDefinitionList = $roleDefinitionList;
        $this->purpose = $purpose;
    }

    /**
     * @param Item[] $items
     * @return void
     */
    public function sortRoles(array &$items): void
    {
        uasort(
            $items,
            function (Item $itemA, Item $itemB) {
                return $itemA->getWeight() <=> $itemB->getWeight();
            }
        );
    }

    public function getProfiles(): array
    {
        $this->roleDefinitionList->buildDefinitions();
        $items = $this->roleDefinitionList->getItems($this->purpose, Item::TYPE_PROFILE);

        $this->sortRoles($items);

        return $items;
    }

    public function getRoles(): array
    {
        $this->roleDefinitionList->buildDefinitions();
        $items = $this->roleDefinitionList->getItems($this->purpose, Item::TYPE_ROLE);

        foreach ($items as $item) {
            foreach ($item->getChildren() as $child) {
                if (array_key_exists($child->getCode(), $items)) {
                    unset($items[$child->getCode()]);
                }
            }
        }

        $this->sortRoles($items);

        return $items;
    }

    /**
     * @param Item $role
     * @return Item[]
     */
    public function getRoleChildren(Item $role): array
    {
        $list = [];
        foreach ($role->getChildren() as $child) {
            if (
                $child->getType() === Item::TYPE_ROLE &&
                ($child->getPurpose() === null || $child->getPurpose() === $this->purpose)
            ) {
                $list[$child->getCode()] = $child;
            }
        }

        $this->sortRoles($list);

        return $list;
    }

    public function hasRole(array $roleCodes, Item $role): bool
    {
        return in_array($role->getCode(), $roleCodes, true);
    }

    /**
     * @param string[] $roleCodes
     * @return bool
     */
    public function validateRoles(array $roleCodes): bool
    {
        $this->roleDefinitionList->buildDefinitions();
        $validCodes = array_keys($this->roleDefinitionList->getItems($this->purpose));

        return count(array_diff($roleCodes, $validCodes)) === 0;
    }
}
