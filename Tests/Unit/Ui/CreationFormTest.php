<?php
namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Form;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Entity\User;
use Spipu\UserBundle\Ui\CreationForm;
use Spipu\UserBundle\Ui\ProfileForm;
use Symfony\Component\Form\FormInterface;

class CreationFormTest extends TestCase
{
    public function testForm()
    {
        $form = new CreationForm(SymfonyMock::getUserPasswordEncoder($this));

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('user_creation', $definition->getCode());
        $this->assertSame(User::class, $definition->getEntityClassName());

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

        $fieldSet = $definition->getFieldSet('password');
        $this->assertInstanceOf(Form\FieldSet::class, $fieldSet);

        $field = $fieldSet->getField('plainPassword');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(
            \Symfony\Component\Form\Extension\Core\Type\RepeatedType::class,
            $field->getType()
        );
        $this->assertSame(
            \Symfony\Component\Form\Extension\Core\Type\PasswordType::class,
            $field->getOptions()['type']
        );
    }

    public function testSubmitOk()
    {
        $form = new CreationForm(SymfonyMock::getUserPasswordEncoder($this));

        $symfonyForm = $this->createMock(FormInterface::class);

        $user = new GenericUser();
        $user->setPlainPassword('mock_password');

        $this->assertSame(null, $user->getPassword());
        $form->setSpecificFields($symfonyForm, $user);
        $this->assertSame('encoded_mock_password', $user->getPassword());
    }

    public function testSubmitKo()
    {
        $form = new CreationForm(SymfonyMock::getUserPasswordEncoder($this));

        $symfonyForm = $this->createMock(FormInterface::class);

        $user = new GenericUser();
        $user->setPlainPassword('');

        $this->assertSame(null, $user->getPassword());
        $this->expectException(\Exception::class);
        $form->setSpecificFields($symfonyForm, $user);
    }
}
