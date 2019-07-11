<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Ui;

use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractForm implements EntityDefinitionInterface
{
    /**
     * @var Form
     */
    protected $definition;

    /**
     * @return Form
     */
    public function getDefinition(): Form
    {
        if (!$this->definition) {
            $this->prepareForm();
        }

        return $this->definition;
    }

    /**
     * @return void
     */
    abstract protected function prepareForm(): void;

    /**
     * @param FormInterface $form
     * @param EntityInterface|null $resource
     * @return void
     */
    abstract public function setSpecificFields(FormInterface $form, EntityInterface $resource = null): void;
}
