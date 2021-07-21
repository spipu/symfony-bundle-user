<?php
declare(strict_types=1);

namespace Spipu\UserBundle\Ui;

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Exception\FormException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Users Creation
 */
class ProfileForm extends AbstractForm
{
    /**
     * @return void
     * @throws FormException
     */
    protected function prepareForm(): void
    {
        $this->definition = new Form('user_profile', $this->getEntityClassName());

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
            )
            ->addFieldSet(
                (new FieldSet('log_in', 'spipu.user.fieldset.log_in', 20))
                    ->setCssClass('col-xs-12 col-md-6')
                    ->addField(new Field(
                        'email',
                        Type\EmailType::class,
                        10,
                        [
                            'label'    => 'spipu.user.field.email',
                            'required' => true,
                            'trim'     => true
                        ]
                    ))
                    ->addField(new Field(
                        'username',
                        Type\TextType::class,
                        20,
                        [
                            'label'    => 'spipu.user.field.username',
                            'required' => true,
                            'trim'     => true
                        ]
                    ))
            )
        ;
    }

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void
    {
    }
}
