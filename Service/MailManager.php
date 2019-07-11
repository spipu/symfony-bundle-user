<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

use Spipu\CoreBundle\Service\MailManager as BaseMailManager;
use Spipu\UserBundle\Entity\GenericUser;
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
     * MailManager constructor.
     * @param BaseMailManager $mailManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param TranslatorInterface $translator
     * @param UserTokenManager $userTokenManager
     */
    public function __construct(
        BaseMailManager $mailManager,
        UrlGeneratorInterface $urlGenerator,
        TranslatorInterface $translator,
        UserTokenManager $userTokenManager
    ) {
        $this->mailManager = $mailManager;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->userTokenManager = $userTokenManager;
    }

    /**
     * @param GenericUser $user
     * @return void
     * @throws \Exception
     */
    public function sendActivationEmail(GenericUser $user): void
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
            $this->translator->trans('spipu.user.email.global.from'),
            $user->getEmail(),
            '@SpipuUser/email/confirm.html.twig',
            [
                'user' => $user,
                'confirmLink' => $confirmLink,
            ]
        );
    }

    /**
     * @param GenericUser $user
     * @return void
     * @throws \Exception
     */
    public function sendRecoveryEmail(GenericUser $user): void
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
            $this->translator->trans('spipu.user.email.global.from'),
            $user->getEmail(),
            '@SpipuUser/email/recover.html.twig',
            [
                'user' => $user,
                'confirmLink' => $confirmLink,
            ]
        );
    }
}
