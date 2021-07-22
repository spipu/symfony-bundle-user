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

use Spipu\UiBundle\Entity\Form\Form;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Service\Ui\Definition\EntityDefinitionInterface;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Component\Form\FormInterface;

abstract class AbstractForm implements EntityDefinitionInterface
{
    /**
     * @var ModuleConfigurationInterface
     */
    private $moduleConfiguration;

    /**
     * @var Form
     */
    protected $definition;

    /**
     * AbstractForm constructor.
     * @param ModuleConfigurationInterface $moduleConfiguration
     */
    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration
    ) {
        $this->moduleConfiguration = $moduleConfiguration;
    }

    /**
     * @return string
     */
    protected function getEntityClassName(): string
    {
        return $this->moduleConfiguration->getEntityClassName();
    }

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
