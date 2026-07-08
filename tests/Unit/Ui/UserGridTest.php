<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Form\Options\YesNo;
use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;
use Spipu\UserBundle\Ui\UserGrid;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(UserGrid::class)]
class UserGridTest extends TestCase
{
    /**
     * @return UserGrid
     */
    public function testGrid(): void
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $yesNo =  new YesNo();

        $grid = new UserGrid($moduleConfiguration, $yesNo);
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

        // The mutating row/mass actions are provided by the consumer app, not shipped by the bundle
        $this->assertCount(1, $definition->getRowActions());
        $this->assertCount(0, $definition->getMassActions());
        $this->assertNull($definition->getRowAction('edit'));
        $this->assertNull($definition->getRowAction('enable'));
        $this->assertNull($definition->getRowAction('disable'));

        $action = $definition->getGlobalAction('create');
        $this->assertInstanceOf(Grid\Action::class, $action);
        $this->assertSame('spipu_user_admin_create', $action->getRouteName());
        $this->assertSame('ROLE_ADMIN_MANAGE_USER_EDIT', $action->getNeededRole());
    }
}
