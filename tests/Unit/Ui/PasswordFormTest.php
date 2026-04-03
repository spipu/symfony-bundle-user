<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Ui;

use Exception;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Form;
use Spipu\UserBundle\Tests\GenericUser;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;
use Spipu\UserBundle\Tests\Unit\Service\UserManagerTest;
use Spipu\UserBundle\Ui\PasswordForm;
use Symfony\Component\Form\FormInterface;

class PasswordFormTest extends TestCase
{
    public function testForm(): void
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new PasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this), UserManagerTest::getService($this));

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('user_password', $definition->getCode());
        $this->assertSame(GenericUser::class, $definition->getEntityClassName());

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

    public function testSubmitOk(): void
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new PasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this), UserManagerTest::getService($this));

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

    public function testSubmitKoBadOldPassword(): void
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new PasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this), UserManagerTest::getService($this));

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm->expects($this->once())->method('offsetGet')->with('oldPassword')->willReturn($symfonyForm);
        $symfonyForm->expects($this->once())->method('getData')->willReturn('bad_password');

        $user = new GenericUser();
        $user->setPassword('encoded_old_password');
        $user->setPlainPassword('new_password');

        $this->assertSame('encoded_old_password', $user->getPassword());
        $this->expectException(Exception::class);
        $form->setSpecificFields($symfonyForm, $user);
    }

    public function testSubmitKoMissingNewPassword(): void
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new PasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this), UserManagerTest::getService($this));

        $symfonyForm = $this->createMock(FormInterface::class);
        $symfonyForm->expects($this->once())->method('offsetGet')->with('oldPassword')->willReturn($symfonyForm);
        $symfonyForm->expects($this->once())->method('getData')->willReturn('old_password');

        $user = new GenericUser();
        $user->setPassword('encoded_old_password');
        $user->setPlainPassword('');

        $this->assertSame('encoded_old_password', $user->getPassword());
        $this->expectException(Exception::class);
        $form->setSpecificFields($symfonyForm, $user);
    }
}
