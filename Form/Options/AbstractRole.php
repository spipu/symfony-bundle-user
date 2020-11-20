<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Form\Options;

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionList;
use Spipu\UiBundle\Form\Options\AbstractOptions;

abstract class AbstractRole extends AbstractOptions
{
    /**
     * @var RoleDefinitionList
     */
    private $roleDefinitionList;

    /**
     * Role constructor.
     * @param RoleDefinitionList $roleDefinitionList
     */
    public function __construct(RoleDefinitionList $roleDefinitionList)
    {
        $this->roleDefinitionList = $roleDefinitionList;
    }

    /**
     * Build the list of the available options
     * @return array
     */
    protected function buildOptions(): array
    {
        $this->roleDefinitionList->buildDefinitions();

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
        $items = $this->roleDefinitionList->getItems($this->getPurpose(), Item::TYPE_PROFILE);

        uasort(
            $items,
            function (Item $itemA, Item  $itemB) {
                return $itemA->getWeight() <=> $itemB->getWeight();
            }
        );

        return $items;
    }

    /**
     * @return array
     */
    private function buildRoles(): array
    {
        $items = $this->roleDefinitionList->getItems($this->getPurpose(), Item::TYPE_ROLE);

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

    /**
     * @return string
     */
    abstract protected function getPurpose(): string;
}
