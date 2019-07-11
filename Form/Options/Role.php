<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Form\Options;

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\UiBundle\Form\Options\AbstractOptions;

class Role extends AbstractOptions
{
    /**
     * @var RoleDefinitionInterface[]
     */
    private $roleDefinitions;

    /**
     * Role constructor.
     * @param iterable $roleDefinitions
     */
    public function __construct(iterable $roleDefinitions)
    {
        $this->roleDefinitions = [];
        foreach ($roleDefinitions as $roleDefinition) {
            $this->roleDefinitions[] = $roleDefinition;
        }
    }

    /**
     * Build the list of the available options
     * @return array
     */
    protected function buildOptions(): array
    {
        foreach ($this->roleDefinitions as $roleDefinition) {
            $roleDefinition->buildDefinition();
        }

        $profiles = $this->buildProfiles();
        $roles = $this->buildRoles();

        $list = [];
        foreach ($profiles as $code => $item) {
            $list[$code] = $item->getLabel();
        }
        foreach ($roles as $code => $item) {
            $list[$code] = $item['label'];
        }

        return $list;
    }

    /**
     * @return Item[]
     * @SuppressWarnings(PMD.StaticAccess)
     */
    private function buildProfiles(): array
    {
        $list = [];
        foreach (Item::getAll() as $item) {
            if ($item->getType() === Item::TYPE_PROFILE) {
                $list[$item->getCode()] = $item;
            }
        }

        uasort(
            $list,
            function (Item $itemA, Item  $itemB) {
                return $itemA->getWeight() <=> $itemB->getWeight();
            }
        );

        return $list;
    }

    /**
     * @return array
     * @SuppressWarnings(PMD.StaticAccess)
     */
    private function buildRoles(): array
    {
        /** @var Item[] $items */
        $items = [];
        foreach (Item::getAll() as $item) {
            if ($item->getType() === Item::TYPE_ROLE) {
                $items[$item->getCode()] = $item;
            }
        }

        $parents = [];
        foreach ($items as $item) {
            foreach ($item->getChildren() as $child) {
                if ($child->getType() === Item::TYPE_ROLE) {
                    $parents[$child->getCode()][] = $item->getCode();
                }
            }
        }

        foreach ($items as $code => $item) {
            if (array_key_exists($item->getCode(), $parents)) {
                unset($items[$code]);
            }
        }

        $list = [];
        $this->buildRolesChildren($list, $items);

        return $list;
    }

    /**
     * @param array $list
     * @param Item[] $children
     * @param int $level
     * @return void
     */
    private function buildRolesChildren(array &$list, array $children, int $level = 0): void
    {
        uasort(
            $children,
            function (Item $itemA, Item  $itemB) {
                return $itemA->getWeight() <=> $itemB->getWeight();
            }
        );

        foreach ($children as $child) {
            if ($child->getType() !== Item::TYPE_ROLE) {
                continue;
            }
            $list[$child->getCode()] = ['label' => $child->getLabel(), 'level' => $level];
            if (count($child->getChildren()) > 0) {
                $this->buildRolesChildren($list, $child->getChildren(), $level + 1);
            }
        }
    }

    /**
     * @return string
     */
    public function getDefaultValue(): string
    {
        return 'ROLE_USER';
    }
}
