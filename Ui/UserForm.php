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

use Spipu\UiBundle\Exception\FormException;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Form\Options\YesNo;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

/**
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class UserForm extends AbstractForm
{
    /**
     * @var YesNo
     */
    private $yesNoOptions;

    /**
     * UserForm constructor.
     * @param ModuleConfigurationInterface $moduleConfiguration
     * @param YesNo $yesNoOptions
     */
    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration,
        YesNo $yesNoOptions
    ) {
        parent::__construct($moduleConfiguration);

        $this->yesNoOptions = $yesNoOptions;
    }

    /**
     * @return void
     * @throws FormException
     * @SuppressWarnings(PMD.ExcessiveMethodLength)
     */
    protected function prepareForm(): void
    {
        $this->definition = new Form('user_admin', $this->getEntityClassName());

        $this->definition
            ->addFieldSet(
                (new FieldSet('information', 'spipu.user.fieldset.information', 10))
                    ->setCssClass('col-xs-12 col-md-6')
                    ->addField(new Field(
                        'firstname',
                        Type\TextType::class,
                        10,
                        [
                            'label'    => 'spipu.user.field.first_name',
                            'required' => true,
                            'trim'     => true
                        ]
                    ))
                    ->addField(new Field(
                        'lastname',
                        Type\TextType::class,
                        20,
                        [
                            'label'    => 'spipu.user.field.last_name',
                            'required' => true,
                            'trim'     => true
                        ]
                    ))
                    ->addField(new Field(
                        'email',
                        Type\EmailType::class,
                        30,
                        [
                            'label'    => 'spipu.user.field.email',
                            'required' => true,
                            'trim'     => true
                        ]
                    ))
                    ->addField(new Field(
                        'username',
                        Type\TextType::class,
                        40,
                        [
                            'label'    => 'spipu.user.field.username',
                            'required' => true,
                            'trim'     => true
                        ]
                    ))
                    ->addField(
                        (new Field(
                            'active',
                            Type\ChoiceType::class,
                            50,
                            [
                                'label'    => 'spipu.user.field.active',
                                'expanded' => false,
                                'choices'  => $this->yesNoOptions,
                                'required' => true,
                            ]
                        ))->setTemplateView('@SpipuUi/entity/view/yes-no.html.twig')
                    )
            )
            ->addFieldSet(
                (new FieldSet('others', 'spipu.user.fieldset.others', 20))
                    ->useHiddenInForm()
                    ->setCssClass('col-xs-12 col-md-6')
                    ->addField(
                        (new Field(
                            'id',
                            Type\IntegerType::class,
                            10,
                            [
                                'label'    => 'spipu.user.field.id',
                            ]
                        ))->useHiddenInForm()
                    )
                    ->addField(
                        (new Field(
                            'nbLogin',
                            Type\IntegerType::class,
                            20,
                            [
                                'label'    => 'spipu.user.field.nb_login',
                            ]
                        ))->useHiddenInForm()
                    )
                    ->addField(
                        (new Field(
                            'nbTryLogin',
                            Type\IntegerType::class,
                            30,
                            [
                                'label'    => 'spipu.user.field.nb_try_login',
                            ]
                        ))->useHiddenInForm()
                    )
                    ->addField(
                        (new Field(
                            'passwordDate',
                            Type\DateTimeType::class,
                            35,
                            [
                                'label'    => 'spipu.user.field.password_date',
                            ]
                        ))->useHiddenInForm()
                    )
                    ->addField(
                        (new Field(
                            'tokenDate',
                            Type\DateTimeType::class,
                            40,
                            [
                                'label'    => 'spipu.user.field.token_date',
                            ]
                        ))->useHiddenInForm()
                    )
                    ->addField(
                        (new Field(
                            'createdAt',
                            Type\DateTimeType::class,
                            50,
                            [
                                'label'    => 'spipu.user.field.created_at',
                            ]
                        ))->useHiddenInForm()
                    )
                    ->addField(
                        (new Field(
                            'updatedAt',
                            Type\DateTimeType::class,
                            60,
                            [
                                'label'    => 'spipu.user.field.updated_at',
                            ]
                        ))->useHiddenInForm()
                    )
            )
        ;
    }

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void
    {
    }
}
