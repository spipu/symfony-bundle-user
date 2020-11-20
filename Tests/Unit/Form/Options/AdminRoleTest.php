<?php
namespace Spipu\UserBundle\Tests\Unit\Form\Options;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Entity\Role\Item;
use Spipu\CoreBundle\Service\RoleDefinitionList;
use Spipu\UiBundle\Form\Options\OptionsInterface;
use Spipu\UserBundle\Form\Options\AdminRole;
use Spipu\CoreBundle\Service\RoleDefinition as CoreRoleDefinition;
use Spipu\UserBundle\Service\RoleDefinition as UserRoleDefinition;

class AdminRoleTest extends TestCase
{
    public function testEmptyRole()
    {
        Item::resetAll();

        $roleDefinitionList = new RoleDefinitionList([]);
        $options =  new AdminRole($roleDefinitionList);

        $this->assertInstanceOf(OptionsInterface::class, $options);

        $this->assertSame('ROLE_USER', $options->getDefaultValue());
        $this->assertSame([], $options->getOptions());

        Item::resetAll();
    }

    public function testRoles()
    {
        Item::resetAll();

        $roleDefinitionList = new RoleDefinitionList([
                new CoreRoleDefinition(),
                new UserRoleDefinition(),
        ]);

        $options =  new AdminRole($roleDefinitionList);

        $this->assertSame(
            [
                'ROLE_USER' => 'spipu.core.role.user',
                'ROLE_ADMIN' => 'spipu.core.role.admin',
                'ROLE_SUPER_ADMIN' => 'spipu.core.role.super_admin',
                'ROLE_ADMIN_MANAGE_USER' => 'spipu.user.role.admin',
                'ROLE_ADMIN_MANAGE_USER_SHOW' => 'spipu.user.role.admin_show',
                'ROLE_ADMIN_MANAGE_USER_EDIT' => 'spipu.user.role.admin_edit',
                'ROLE_ADMIN_MANAGE_USER_DELETE' => 'spipu.user.role.admin_delete',
            ],
            $options->getOptions()
        );

        Item::resetAll();
    }
}
