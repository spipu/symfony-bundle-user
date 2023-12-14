<?php
namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;
use Spipu\UserBundle\Ui\RecoveryForm;
use Symfony\Component\Form\FormInterface;

class RecoveryFormTest extends TestCase
{
    public function testForm()
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new RecoveryForm($moduleConfiguration);

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('user_recovery', $definition->getCode());
        $this->assertSame(null, $definition->getEntityClassName());

        $fieldSet = $definition->getFieldSet('log_in');
        $this->assertInstanceOf(Form\FieldSet::class, $fieldSet);

        $field = $fieldSet->getField('email');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\EmailType::class, $field->getType());

        $symfonyForm = $this->createMock(FormInterface::class);
        $form->setSpecificFields($symfonyForm, null);
    }
}
