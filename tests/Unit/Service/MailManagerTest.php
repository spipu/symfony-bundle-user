<?php

declare(strict_types=1);

namespace Spipu\UserBundle\Tests\Unit\Service;

use DateTimeInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Tests\SpipuCoreMock;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Service\MailManager;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AllowMockObjectsWithoutExpectations]
#[CoversClass(MailManager::class)]
class MailManagerTest extends TestCase
{
    public function testActivationEmail(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setEmail('mock_email')
            ->setUsername('mock_username');

        $user->setCreatedAtValue();
        $user->setUpdatedAtValue();

        $router = SymfonyMock::getRouter($this);
        $router
            ->expects($this->once())
            ->method('generate')
            ->with(
                'spipu_user_account_create_confirm',
                [
                    'email' => $user->getEmail(),
                    'token' => 'mock_token_42'
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

        $mailManager = SpipuCoreMock::getMailManager($this);
        $mailManager
            ->expects($this->once())
            ->method('sendTwigMail')
            ->with(
                'spipu.user.email.confirm.title',
                'no-reply@mysite.fr',
                $user->getEmail(),
                '@SpipuUser/email/confirm.html.twig',
                [
                    'user' => $user,
                    'confirmLink' => '/spipu_user_account_create_confirm/?email=mock_email&token=mock_token_42',
                ]
            );

        $userTokenManager = SpipuUserMock::getUserTokenManager($this);

        $mailConfiguration = MailConfigurationTest::getService($this);

        $service = new MailManager(
            $mailManager,
            $router,
            SymfonyMock::getTranslator($this),
            $userTokenManager,
            $mailConfiguration
        );

        $this->assertSame(null, $user->getTokenDate());
        $service->sendActivationEmail($user);
        $this->assertInstanceOf(DateTimeInterface::class, $user->getTokenDate());

        $this->assertTrue($userTokenManager->isValid($user, 'mock_token_42'));
        $userTokenManager->reset($user);
        $this->assertFalse($userTokenManager->isValid($user, 'mock_token_42'));
    }

    public function testRecoveryEmail(): void
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setEmail('mock_email')
            ->setUsername('mock_username');

        $user->setCreatedAtValue();
        $user->setUpdatedAtValue();

        $router = SymfonyMock::getRouter($this);
        $router
            ->expects($this->once())
            ->method('generate')
            ->with(
                'spipu_user_account_recovery_confirm',
                [
                    'email' => $user->getEmail(),
                    'token' => 'mock_token_42'
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

        $mailManager = SpipuCoreMock::getMailManager($this);
        $mailManager
            ->expects($this->once())
            ->method('sendTwigMail')
            ->with(
                'spipu.user.email.recover.title',
                'no-reply@mysite.fr',
                $user->getEmail(),
                '@SpipuUser/email/recover.html.twig',
                [
                    'user' => $user,
                    'confirmLink' => '/spipu_user_account_recovery_confirm/?email=mock_email&token=mock_token_42',
                ]
            );

        $userTokenManager = SpipuUserMock::getUserTokenManager($this);

        $mailConfiguration = MailConfigurationTest::getService($this);

        $service = new MailManager(
            $mailManager,
            $router,
            SymfonyMock::getTranslator($this),
            $userTokenManager,
            $mailConfiguration
        );

        $this->assertSame(null, $user->getTokenDate());
        $service->sendRecoveryEmail($user);
        $this->assertInstanceOf(DateTimeInterface::class, $user->getTokenDate());
    }

    public function testEmailChangeNotification(): void
    {
        $router = SymfonyMock::getRouter($this);

        $mailManager = SpipuCoreMock::getMailManager($this);
        $mailManager
            ->expects($this->once())
            ->method('sendTwigMail')
            ->with(
                'spipu.user.email.email_change_notification.title',
                'no-reply@mysite.fr',
                'old@test.fr',
                '@SpipuUser/email/change-notification.html.twig',
                [
                    'oldEmail' => 'old@test.fr',
                    'newEmail' => 'new@test.fr',
                ]
            );

        $userTokenManager = SpipuUserMock::getUserTokenManager($this);
        $mailConfiguration = MailConfigurationTest::getService($this);

        $service = new MailManager(
            $mailManager,
            $router,
            SymfonyMock::getTranslator($this),
            $userTokenManager,
            $mailConfiguration
        );

        $service->sendEmailChangeNotification('old@test.fr', 'new@test.fr');
    }

}
