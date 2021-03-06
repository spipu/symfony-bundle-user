<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Ui;

use Exception;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Account Creation
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class CreationForm extends ProfileForm
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserForm constructor.
     * @param ModuleConfigurationInterface $moduleConfiguration
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration,
        UserPasswordEncoderInterface $encoder
    ) {
        parent::__construct($moduleConfiguration);

        $this->encoder = $encoder;
    }

    /**
     * @return void
     * @throws \Spipu\UiBundle\Exception\FormException
     */
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
                            'first_options'  => array('label' => 'spipu.user.field.password'),
                            'second_options' => array('label' => 'spipu.user.field.confirm'),
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
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
        /** @var UserInterface $resource */
        if (empty($resource->getPlainPassword())) {
            throw new Exception('The password is required');
        }

        $resource->setPassword($this->encoder->encodePassword($resource, $resource->getPlainPassword()));
    }
}
