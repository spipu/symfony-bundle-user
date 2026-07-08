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

use InvalidArgumentException;
use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionList;
use Spipu\UserBundle\Exception\ForbiddenRoleException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoleService
{
    private RoleDefinitionList $roleDefinitionList;
    private AuthorizationCheckerInterface $authorizationChecker;
    private string $purpose;

    public function __construct(
        RoleDefinitionList $roleDefinitionList,
        AuthorizationCheckerInterface $authorizationChecker,
        string $purpose = 'admin'
    ) {
        $this->roleDefinitionList = $roleDefinitionList;
        $this->authorizationChecker = $authorizationChecker;
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
        $items = $this->filterGranted($this->roleDefinitionList->getItems($this->purpose, Item::TYPE_PROFILE));

        $this->sortRoles($items);

        return $items;
    }

    public function getRoles(): array
    {
        $this->roleDefinitionList->buildDefinitions();
        $items = $this->filterGranted($this->roleDefinitionList->getItems($this->purpose, Item::TYPE_ROLE));

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
                ($child->getPurpose() === null || $child->getPurpose() === $this->purpose) &&
                $this->authorizationChecker->isGranted($child->getCode())
            ) {
                $list[$child->getCode()] = $child;
            }
        }

        $this->sortRoles($list);

        return $list;
    }

    /**
     * @param Item $role
     * @return string[]
     */
    public function getProfileRoleList(Item $role): array
    {
        if ($role->getType() !== Item::TYPE_PROFILE) {
            throw new InvalidArgumentException('Role is not a profile');
        }
        $list = [];
        foreach ($role->getChildren() as $child) {
            if ($child->getType() === Item::TYPE_PROFILE) {
                $list = [...$list, ...$this->getProfileRoleList($child)];
                continue;
            }
            if (
                $child->getType() === Item::TYPE_ROLE &&
                ($child->getPurpose() === null || $child->getPurpose() === $this->purpose)
            ) {
                $list[] = $child->getCode();
            }
        }
        return array_values(array_unique($list));
    }

    public function hasRole(array $roleCodes, Item $role): bool
    {
        return in_array($role->getCode(), $roleCodes, true);
    }

    /**
     * Keep only the items the acting user owns himself (a user only sees the roles he can grant).
     *
     * @param Item[] $items
     * @return Item[]
     */
    private function filterGranted(array $items): array
    {
        return array_filter(
            $items,
            fn(Item $item): bool => $this->authorizationChecker->isGranted($item->getCode())
        );
    }

    /**
     * The acting user can edit a target's ACL only if he owns every role the target currently has,
     * i.e. the target does not have more rights than the acting user.
     *
     * @param string[] $currentRoles
     * @return bool
     */
    public function canEditRoles(array $currentRoles): bool
    {
        foreach ($currentRoles as $code) {
            if (!$this->authorizationChecker->isGranted($code)) {
                return false;
            }
        }

        return true;
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

    /**
     * Restrict the submitted roles to what the acting user is allowed to grant (only roles he owns himself).
     *
     * @param string[] $submittedCodes
     * @return string[]
     * @throws ForbiddenRoleException
     */
    public function computeRolesToSave(array $submittedCodes): array
    {
        foreach ($submittedCodes as $code) {
            if (!$this->authorizationChecker->isGranted($code)) {
                throw new ForbiddenRoleException($code);
            }
        }

        return array_values(array_unique($submittedCodes));
    }
}
