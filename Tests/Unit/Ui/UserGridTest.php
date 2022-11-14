<?php
namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Form\Options\YesNo;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;
use Spipu\UserBundle\Ui\UserGrid;

class UserGridTest extends TestCase
{
    /**
     * @return UserGrid
     */
    public function testGrid()
    {
        $user = SpipuUserMock::getUserEntity(42);

        $tokenStorage = SymfonyMock::getTokenStorage($this);
        $tokenStorage
            ->getToken()
            ->method('getUser')
            ->willReturn($user);

        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $yesNo =  new YesNo();

        $grid = new UserGrid($moduleConfiguration, $tokenStorage, $yesNo);
        $this->assertInstanceOf(GridDefinitionInterface::class, $grid);

        $definition = $grid->getDefinition();
        $this->assertInstanceOf(Grid\Grid::class, $definition);

        $this->assertSame('user', $definition->getCode());
        $this->assertSame($moduleConfiguration->getEntityName(), $definition->getEntityName());

        $column = $definition->getColumn('id');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_INTEGER, $column->getType()->getType());

        $column = $definition->getColumn('username');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_TEXT, $column->getType()->getType());

        $column = $definition->getColumn('email');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_TEXT, $column->getType()->getType());

        $column = $definition->getColumn('is_active');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_SELECT, $column->getType()->getType());

        $column = $definition->getColumn('nb_login');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_INTEGER, $column->getType()->getType());

        $column = $definition->getColumn('updated_at');
        $this->assertInstanceOf(Grid\Column::class, $column);
        $this->assertSame(Grid\ColumnType::TYPE_DATETIME, $column->getType()->getType());

        $action = $definition->getRowAction('show');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_show', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_SHOW', $action->getNeededRole());

        $action = $definition->getRowAction('edit');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_edit', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_EDIT', $action->getNeededRole());

        $action = $definition->getRowAction('enable');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_enable', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_EDIT', $action->getNeededRole());
        $this->assertSame(['id' => ['neq' => 42], 'active' => ['neq' => 1]], $action->getConditions());

        $action = $definition->getRowAction('disable');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_disable', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_EDIT', $action->getNeededRole());
        $this->assertSame(['id' => ['neq' => 42], 'active' => ['eq' => 1]], $action->getConditions());

        $action = $definition->getMassAction('enable');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_mass_enable', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_EDIT', $action->getNeededRole());

        $action = $definition->getMassAction('disable');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_mass_disable', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_EDIT', $action->getNeededRole());
    }
}
