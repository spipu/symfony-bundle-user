<?php
namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\UiBundle\Entity\Form;
use Spipu\UserBundle\Tests\GenericUser;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;
use Spipu\UserBundle\Ui\ProfileForm;
use Symfony\Component\Form\FormInterface;

class ProfileFormTest extends TestCase
{
    public function testForm()
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new ProfileForm($moduleConfiguration);

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('user_profile', $definition->getCode());
        $this->assertSame(GenericUser::class, $definition->getEntityClassName());

        $fieldSet = $definition->getFieldSet('information');
        $this->assertInstanceOf(Form\FieldSet::class, $fieldSet);

        $field = $fieldSet->getField('firstname');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\TextType::class, $field->getType());

        $field = $fieldSet->getField('lastname');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\TextType::class, $field->getType());

        $fieldSet = $definition->getFieldSet('log_in');
        $this->assertInstanceOf(Form\FieldSet::class, $fieldSet);

        $field = $fieldSet->getField('email');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\EmailType::class, $field->getType());

        $field = $fieldSet->getField('username');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\TextType::class, $field->getType());

        $symfonyForm = $this->createMock(FormInterface::class);
        $form->setSpecificFields($symfonyForm, null);
    }
}
