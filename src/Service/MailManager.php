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

namespace Spipu\UserBundle\Service;

use Spipu\CoreBundle\Service\MailManager as BaseMailManager;
use Spipu\UserBundle\Entity\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailManager
{
    private BaseMailManager $mailManager;
    private UrlGeneratorInterface $urlGenerator;
    private TranslatorInterface $translator;
    private UserTokenManager $userTokenManager;
    private MailConfigurationInterface $mailConfiguration;

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
