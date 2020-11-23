<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionList;

class RoleService
{
    /**
     * @var RoleDefinitionList
     */
    private $roleDefinitionList;

    /**
     * @var string
     */
    private $purpose;

    /**
     * RoleService constructor.
     * @param RoleDefinitionList $roleDefinitionList
     * @param string $purpose
     */
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
            function (Item $itemA, Item  $itemB) {
                return $itemA->getWeight() <=> $itemB->getWeight();
            }
        );
    }

    /**
     * @return array
     */
    public function getProfiles(): array
    {
        $this->roleDefinitionList->buildDefinitions();
        $items = $this->roleDefinitionList->getItems($this->purpose, Item::TYPE_PROFILE);

        $this->sortRoles($items);

        return $items;
    }

    /**
     * @return array
     */
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
            if ($child->getType() === Item::TYPE_ROLE &&
                ($child->getPurpose() === null || $child->getPurpose() === $this->purpose)
            ) {
                $list[$child->getCode()] = $child;
            }
        }

        $this->sortRoles($list);

        return $list;
    }

    /**
     * @param array $roleCodes
     * @param Item $role
     * @return bool
     */
    public function hasRole(array $roleCodes, Item $role): bool
    {
        return in_array($role->getCode(), $roleCodes);
    }
}
