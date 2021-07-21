<?php
declare(strict_types=1);

namespace Spipu\UserBundle\Service;

use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;

class RoleDefinition implements RoleDefinitionInterface
{
    /**
     * @return void
     */
    public function buildDefinition(): void
    {
        Item::load('ROLE_ADMIN_MANAGE_USER_SHOW')
            ->setLabel('spipu.user.role.admin_show')
            ->setWeight(10)
            ->addChild('ROLE_ADMIN');

        Item::load('ROLE_ADMIN_MANAGE_USER_EDIT')
            ->setLabel('spipu.user.role.admin_edit')
            ->setWeight(20)
            ->addChild('ROLE_ADMIN');

        Item::load('ROLE_ADMIN_MANAGE_USER_DELETE')
            ->setLabel('spipu.user.role.admin_delete')
            ->setWeight(30)
            ->addChild('ROLE_ADMIN');

        Item::load('ROLE_ADMIN_MANAGE_USER')
            ->setLabel('spipu.user.role.admin')
            ->setWeight(210)
            ->addChild('ROLE_ADMIN_MANAGE_USER_SHOW')
            ->addChild('ROLE_ADMIN_MANAGE_USER_EDIT')
            ->addChild('ROLE_ADMIN_MANAGE_USER_DELETE');

        Item::load('ROLE_SUPER_ADMIN')
            ->addChild('ROLE_ADMIN_MANAGE_USER');
    }
}
