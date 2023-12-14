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

namespace Spipu\UserBundle\Ui;

use Spipu\UiBundle\Service\Ui\Definition\GridDefinitionInterface;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UiBundle\Entity\Grid;
use Spipu\UiBundle\Form\Options\YesNo as OptionsYesNo;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserGrid implements GridDefinitionInterface
{
    private ModuleConfigurationInterface $moduleConfiguration;
    private TokenStorageInterface $tokenStorage;
    private OptionsYesNo $optionsYesNo;

    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration,
        TokenStorageInterface $tokenStorage,
        OptionsYesNo $optionsYesNo
    ) {
        $this->moduleConfiguration = $moduleConfiguration;
        $this->tokenStorage = $tokenStorage;
        $this->optionsYesNo = $optionsYesNo;
    }

    private ?Grid\Grid $definition = null;

    public function getDefinition(): Grid\Grid
    {
        if (!$this->definition) {
            $this->prepareGrid();
        }

        return $this->definition;
    }

    private function prepareGrid(): void
    {
        $currentUser = $this->getCurrentUser();

        $this->definition = (new Grid\Grid('user', $this->moduleConfiguration->getEntityName()))
            ->setPager(
                (new Grid\Pager([10, 20, 50, 100], 20))
            )
            ->addColumn(
                (new Grid\Column('id', 'spipu.user.field.id', 'id', 10))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_INTEGER)))
                    ->setFilter((new Grid\ColumnFilter(true, true))->useRange())
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('username', 'spipu.user.field.username', 'username', 20))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
                    ->setFilter((new Grid\ColumnFilter(true, true)))
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('email', 'spipu.user.field.email', 'email', 30))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_TEXT)))
                    ->setFilter((new Grid\ColumnFilter(true, true)))
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('is_active', 'spipu.user.field.active', 'active', 40))
                    ->setType(
                        (new Grid\ColumnType(Grid\ColumnType::TYPE_SELECT))
                            ->setOptions($this->optionsYesNo)
                            ->setTemplateField('@SpipuUi/grid/field/yes-no.html.twig')
                    )
                    ->setFilter((new Grid\ColumnFilter(true, false)))
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('nb_login', 'spipu.user.field.nb_login', 'nbLogin', 50))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_INTEGER)))
                    ->setFilter((new Grid\ColumnFilter(true))->useRange())
                    ->useSortable()
            )
            ->addColumn(
                (new Grid\Column('updated_at', 'spipu.user.field.updated_at', 'updatedAt', 60))
                    ->setType((new Grid\ColumnType(Grid\ColumnType::TYPE_DATETIME)))
                    ->setFilter((new Grid\ColumnFilter(true))->useRange())
                    ->useSortable()
            )
            ->setDefaultSort('id')
            ->addRowAction(
                (new Grid\Action('show', 'spipu.ui.action.show', 10, 'spipu_user_admin_show'))
                    ->setCssClass('primary')
                    ->setIcon('eye')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_USER_SHOW')
            )
            ->addRowAction(
                (new Grid\Action('edit', 'spipu.ui.action.edit', 20, 'spipu_user_admin_edit'))
                    ->setCssClass('success')
                    ->setIcon('edit')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_USER_EDIT')
            )
            ->addRowAction(
                (new Grid\Action('enable', 'spipu.user.action.enable', 30, 'spipu_user_admin_enable'))
                    ->setCssClass('info')
                    ->setIcon('check')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_USER_EDIT')
                    ->setConditions(['id' => ['neq' => $currentUser->getId()], 'active' => ['neq' => 1]])
            )
            ->addRowAction(
                (new Grid\Action('disable', 'spipu.user.action.disable', 40, 'spipu_user_admin_disable'))
                    ->setCssClass('warning')
                    ->setIcon('times')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_USER_EDIT')
                    ->setConditions(['id' => ['neq' => $currentUser->getId()], 'active' => ['eq' => 1]])
            )
            ->addMassAction(
                (new Grid\Action('enable', 'spipu.user.action.enable', 50, 'spipu_user_admin_mass_enable'))
                    ->setCssClass('info')
                    ->setIcon('check')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_USER_EDIT')
            )
            ->addMassAction(
                (new Grid\Action('disable', 'spipu.user.action.disable', 60, 'spipu_user_admin_mass_disable'))
                    ->setCssClass('warning')
                    ->setIcon('times')
                    ->setNeededRole('ROLE_ADMIN_MANAGE_USER_EDIT')
            )
        ;
    }

    private function getCurrentUser(): UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->tokenStorage->getToken()->getUser();

        return $user;
    }
}
