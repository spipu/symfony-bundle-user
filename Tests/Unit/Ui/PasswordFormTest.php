<?php
namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Form;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Entity\User;
use Spipu\UserBundle\Ui\PasswordForm;
use Symfony\Component\Form\FormInterface;

class PasswordFormTest extends TestCase
{
    public function testForm()
    {
        $form = new PasswordForm(SymfonyMock::getUserPasswordEncoder($this));

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('user_password', $definition->getCode());
        $this->assertSame(User::class, $definition->getEntityClassName());

        $fieldSet = $definition->getFieldSet('old_password');
        $this->assertInstanceOf(Form\FieldSet::class, $fieldSet);

        $field = $fieldSet->getField('oldPassword');
        $this->assertInstanceOf(Form\Field::class, $field);
        $this->assertSame(\Symfony\Component\Form\Extension\Core\Type\PasswordType::class, $field->getType());

        $fieldSet = $definition->getFieldSet('new_password');
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
        $form = new PasswordForm(SymfonyMock::getUserPasswordEncoder($this));

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm->expects($this->once())->method('offsetGet')->with('oldPassword')->willReturn($symfonyForm);
        $symfonyForm->expects($this->once())->method('getData')->willReturn('old_password');

        $user = new GenericUser();
        $user->setPassword('encoded_old_password');
        $user->setPlainPassword('new_password');

        $this->assertSame('encoded_old_password', $user->getPassword());
        $form->setSpecificFields($symfonyForm, $user);
        $this->assertSame('encoded_new_password', $user->getPassword());
    }

    public function testSubmitKoBadOldPassword()
    {
        $form = new PasswordForm(SymfonyMock::getUserPasswordEncoder($this));

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm->expects($this->once())->method('offsetGet')->with('oldPassword')->willReturn($symfonyForm);
        $symfonyForm->expects($this->once())->method('getData')->willReturn('bad_password');

        $user = new GenericUser();
        $user->setPassword('encoded_old_password');
        $user->setPlainPassword('new_password');

        $this->assertSame('encoded_old_password', $user->getPassword());
        $this->expectException(\Exception::class);
        $form->setSpecificFields($symfonyForm, $user);
    }

    public function testSubmitKoMissingNewPassword()
    {
        $form = new PasswordForm(SymfonyMock::getUserPasswordEncoder($this));

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm->expects($this->once())->method('offsetGet')->with('oldPassword')->willReturn($symfonyForm);
        $symfonyForm->expects($this->once())->method('getData')->willReturn('old_password');

        $user = new GenericUser();
        $user->setPassword('encoded_old_password');
        $user->setPlainPassword('');

        $this->assertSame('encoded_old_password', $user->getPassword());
        $this->expectException(\Exception::class);
        $form->setSpecificFields($symfonyForm, $user);
    }
}
