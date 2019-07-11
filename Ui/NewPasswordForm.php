<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Ui;

use Spipu\UiBundle\Exception\FormException;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Entity\User;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class NewPasswordForm extends AbstractForm
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * UserForm constructor.
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        UserPasswordEncoderInterface $encoder
    ) {
        $this->encoder = $encoder;
    }

    /**
     * @return void
     * @throws FormException
     */
    protected function prepareForm(): void
    {
        $this->definition = new Form('user_new_password', User::class);

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
        ;
    }

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
        /** @var GenericUser $resource */
        if (empty($resource->getPlainPassword())) {
            throw new \Exception('The password is required');
        }

        $resource->setPassword($this->encoder->encodePassword($resource, $resource->getPlainPassword()));
    }
}
