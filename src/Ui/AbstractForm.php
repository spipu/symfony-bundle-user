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
    private ModuleConfigurationInterface $moduleConfiguration;
    protected ?Form $definition = null;

    public function __construct(
        ModuleConfigurationInterface $moduleConfiguration
    ) {
        $this->moduleConfiguration = $moduleConfiguration;
    }

    protected function getEntityClassName(): string
    {
        return $this->moduleConfiguration->getEntityClassName();
    }

    public function getDefinition(): Form
    {
        if (!$this->definition) {
            $this->prepareForm();
        }

        return $this->definition;
    }

    abstract protected function prepareForm(): void;

    abstract public function setSpecificFields(FormInterface $form, ?EntityInterface $resource = null): void;
}
