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

use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\Form\Field;
use Spipu\UiBundle\Entity\Form\FieldSet;
use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Exception\FormException;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

/**
 * Account Recovery
 */
class RecoveryForm extends AbstractForm
{
    /**
     * @return void
     * @throws FormException
     */
    protected function prepareForm(): void
    {
        $this->definition = new Form('user_recovery');

        $this->definition
            ->addFieldSet(
                (new FieldSet('log_in', 'spipu.user.fieldset.log_in', 10))
                    ->setCssClass('col-xs-12 col-md-8 m-auto')
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
