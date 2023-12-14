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
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordForm extends AbstractForm
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration,
        UserPasswordHasherInterface $hasher
    ) {
        parent::__construct($moduleConfiguration);

        $this->hasher = $hasher;
    }

    protected function prepareForm(): void
    {
        $this->definition = new Form('user_password', $this->getEntityClassName());

        $this->definition
            ->addFieldSet(
                (new FieldSet('old_password', 'spipu.user.fieldset.old_password', 10))
                    ->useHiddenInView()
                    ->setCssClass('col-xs-12 col-md-6')
                    ->addField((new Field(
                        'oldPassword',
                        Type\PasswordType::class,
                        10,
                        [
                            'label'          => 'spipu.user.field.password',
                            'mapped'         => false,
                            'required'       => true,
                        ]
                    )))
            )
            ->addFieldSet(
                (new FieldSet('new_password', 'spipu.user.fieldset.new_password', 20))
                    ->useHiddenInView()
                    ->setCssClass('col-xs-12 col-md-6')
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
                    )))
            )
        ;
    }

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
        /** @var UserInterface $resource */
        $oldPassword = $form['oldPassword']->getData();
        if (!$this->hasher->isPasswordValid($resource, $oldPassword)) {
            throw new Exception('spipu.user.error.bad_old_password');
        }

        if (empty($resource->getPlainPassword())) {
            throw new Exception('The password is required');
        }

        $resource->setPassword($this->hasher->hashPassword($resource, $resource->getPlainPassword()));
        $resource->setPasswordDate(new DateTime());
    }
}
