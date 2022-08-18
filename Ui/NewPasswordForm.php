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

use Exception;
use Spipu\UiBundle\Exception\FormException;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class NewPasswordForm extends AbstractForm
{
    /**
     * @var UserPasswordHasherInterface
     */
    private $hasher;

    /**
     * UserForm constructor.
     * @param ModuleConfigurationInterface $moduleConfiguration
     * @param UserPasswordHasherInterface $hasher
     */
    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration,
        UserPasswordHasherInterface $hasher
    ) {
        parent::__construct($moduleConfiguration);

        $this->hasher = $hasher;
    }

    /**
     * @return void
     * @throws FormException
     */
    protected function prepareForm(): void
    {
        $this->definition = new Form('user_new_password', $this->getEntityClassName());

        $this->definition
            ->addFieldSet(
                (new FieldSet('new_password', 'spipu.user.fieldset.new_password', 10))
                    ->useHiddenInView()
                    ->setCssClass('col-xs-12 col-md-8 m-auto')
                    ->addField((new Field(
                        'plainPassword',
                        Type\RepeatedType::class,
                        10,
                        [
                            'type'           => Type\PasswordType::class,
                            'first_options'  => array('label' => 'spipu.user.field.password'),
                            'second_options' => array('label' => 'spipu.user.field.confirm'),
                            'required'       => true,
                        ]
                    )))
            )
            ->setValidateSuccessMessage('spipu.user.success.recover')
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
        if (empty($resource->getPlainPassword())) {
            throw new Exception('The password is required');
        }

        $resource->setPassword($this->hasher->hashPassword($resource, $resource->getPlainPassword()));
    }
}
