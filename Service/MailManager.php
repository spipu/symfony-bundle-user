<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

use Spipu\CoreBundle\Service\MailManager as BaseMailManager;
use Spipu\UserBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailManager
{
    /**
     * @var BaseMailManager
     */
    private $mailManager;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var UserTokenManager
     */
    private $userTokenManager;

    /**
     * @var MailConfigurationInterface
     */
    private $mailConfiguration;

    /**
     * MailManager constructor.
     * @param BaseMailManager $mailManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface $translator
     * @param UserTokenManager $userTokenManager
     * @param MailConfigurationInterface $mailConfiguration
     */
    public function __construct(
        BaseMailManager $mailManager,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        UserTokenManager $userTokenManager,
        MailConfigurationInterface $mailConfiguration
    ) {
        $this->mailManager = $mailManager;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->userTokenManager = $userTokenManager;
        $this->mailConfiguration = $mailConfiguration;
    }

    /**
     * @param UserInterface $user
     * @return void
     * @throws \Throwable
     */
    public function sendActivationEmail(UserInterface $user): void
    {
        $confirmLink = $this->urlGenerator->generate(
            'spipu_user_account_create_confirm',
            [
                'email' => $user->getEmail(),
                'token' => $this->userTokenManager->generate($user),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailManager->sendTwigMail(
            $this->translator->trans('spipu.user.email.confirm.title'),
            $this->mailConfiguration->getEmailFrom(),
            $user->getEmail(),
            '@SpipuUser/email/confirm.html.twig',
            [
                'user' => $user,
                'confirmLink' => $confirmLink,
            ]
        );
    }

    /**
     * @param UserInterface $user
     * @return void
     * @throws \Throwable
     */
    public function sendRecoveryEmail(UserInterface $user): void
    {
        $confirmLink = $this->urlGenerator->generate(
            'spipu_user_account_recovery_confirm',
            [
                'email' => $user->getEmail(),
                'token' => $this->userTokenManager->generate($user),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailManager->sendTwigMail(
            $this->translator->trans('spipu.user.email.recover.title'),
            $this->mailConfiguration->getEmailFrom(),
            $user->getEmail(),
            '@SpipuUser/email/recover.html.twig',
            [
                'user' => $user,
                'confirmLink' => $confirmLink,
            ]
        );
    }
}
