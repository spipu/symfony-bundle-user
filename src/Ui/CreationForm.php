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

use DateTime;
use Exception;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Spipu\UserBundle\Service\UserManager;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreationForm extends ProfileForm
{
    private UserPasswordHasherInterface $hasher;
    private UserManager $userManager;

    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration,
        UserPasswordHasherInterface $hasher,
        UserManager $userManager
    ) {
        parent::__construct($moduleConfiguration);

        $this->hasher = $hasher;
        $this->userManager = $userManager;
    }

    protected function prepareForm(): void
    {
        parent::prepareForm();

        $this->definition->setCode('user_creation');

        $this->definition
            ->addFieldSet(
                (new FieldSet('password', 'spipu.user.fieldset.password', 30))
                    ->useHiddenInView()
                    ->setCssClass('col-12')
                    ->addField((new Field(
                        'plainPassword',
                        Type\RepeatedType::class,
                        10,
                        [
                            'type'           => Type\PasswordType::class,
                            'first_options'  => ['label' => 'spipu.user.field.password'],
                            'second_options' => ['label' => 'spipu.user.field.confirm'],
                            'required'       => true,
                        ]
                    ))->useHiddenInView())
            )
        ;
    }

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @throws Exception
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void
    {
        /** @var UserInterface $resource */
        if (empty($resource->getPlainPassword())) {
            throw new Exception('The password is required');
        }

        $this->userManager->validatePassword($resource->getPlainPassword());

        $resource->setPassword($this->hasher->hashPassword($resource, $resource->getPlainPassword()));
        $resource->setPasswordDate(new DateTime());
    }
}
