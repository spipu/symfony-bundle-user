<?php
namespace Spipu\UserBundle\Tests\Unit\Ui;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UiBundle\Entity\Form;
use Spipu\UserBundle\Tests\GenericUser;
use Spipu\UserBundle\Tests\Unit\Service\ModuleConfigurationTest;
use Spipu\UserBundle\Ui\NewPasswordForm;
use Symfony\Component\Form\FormInterface;

class NewPasswordFormTest extends TestCase
{
    public function testForm()
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new NewPasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this));

        $definition = $form->getDefinition();

        $this->assertInstanceOf(Form\Form::class, $definition);

        $this->assertSame('user_new_password', $definition->getCode());
        $this->assertSame(GenericUser::class, $definition->getEntityClassName());

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
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new NewPasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this));

        $symfonyForm = $this->createMock(FormInterface::class);

        $user = new GenericUser();
        $user->setPlainPassword('mock_password');

        $this->assertSame(null, $user->getPassword());
        $form->setSpecificFields($symfonyForm, $user);
        $this->assertSame('encoded_mock_password', $user->getPassword());
    }

    public function testSubmitKo()
    {
        $moduleConfiguration = ModuleConfigurationTest::getService($this, true, true);

        $form = new NewPasswordForm($moduleConfiguration, SymfonyMock::getUserPasswordHasher($this));

        $symfonyForm = $this->createMock(FormInterface::class);

        $user = new GenericUser();
        $user->setPlainPassword('');

        $this->assertSame(null, $user->getPassword());

        $this->expectException(\Exception::class);
        $form->setSpecificFields($symfonyForm, $user);
    }
}
