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

namespace Spipu\UserBundle\Controller;

use Exception;
use Spipu\UiBundle\Service\Ui\FormFactory;
use Spipu\UserBundle\Event\UserEvent;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\MailManager;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Spipu\UserBundle\Service\UserTokenManager;
use Spipu\UserBundle\Ui\CreationForm;
use Spipu\UserBundle\Ui\NewPasswordForm;
use Spipu\UserBundle\Ui\RecoveryForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/account')]
class AccountController extends AbstractController
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    #[Route(path: '/create', name: 'spipu_user_account_create', methods: 'GET|POST')]
    public function create(
        FormFactory $formFactory,
        ModuleConfigurationInterface $moduleConfiguration,
        CreationForm $creationForm,
        MailManager $mailManager
    ): Response {
        if (!$moduleConfiguration->hasAllowAccountCreation()) {
            throw $this->createNotFoundException();
        }

        $resource = $moduleConfiguration->getNewEntity();

        $manager = $formFactory->create($creationForm);
        $manager->setResource($resource);
        $manager->setSubmitButton('spipu.ui.action.create');
        if ($manager->validate()) {
            $this->container->get('request_stack')->getSession()->getFlashBag()->clear();
            try {
                $mailManager->sendActivationEmail($resource);

                $event = new UserEvent($resource, 'create');
                $this->eventDispatcher->dispatch($event, $event->getEventCode());
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
            return $this->redirectToRoute('spipu_user_account_create_waiting');
        }

        return $this->render('@SpipuUser/create.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/create-waiting', name: 'spipu_user_account_create_waiting', methods: 'GET')]
    public function createWaiting(ModuleConfigurationInterface $moduleConfiguration): Response
    {
        if (!$moduleConfiguration->hasAllowAccountCreation()) {
            throw $this->createNotFoundException();
        }

        return $this->render('@SpipuUser/create-waiting.html.twig');
    }

    #[Route(path: '/confirm/{email}/{token}', name: 'spipu_user_account_create_confirm', methods: 'GET')]
    public function createConfirm(
        ModuleConfigurationInterface $moduleConfiguration,
        UserRepository $userRepository,
        UserTokenManager $userTokenManager,
        string $email,
        string $token
    ): Response {
        if (!$moduleConfiguration->hasAllowAccountCreation()) {
            throw $this->createNotFoundException();
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('danger', $this->trans('spipu.user.error.confirm'));
            return $this->redirectToRoute('spipu_user_security_login');
        }

        if (!$userTokenManager->isValid($user, $token)) {
            $this->addFlash('danger', $this->trans('spipu.user.error.confirm'));
            return $this->redirectToRoute('spipu_user_security_login');
        }

        $user->setActive(true);
        $userTokenManager->reset($user);

        $event = new UserEvent($user, 'confirm');
        $this->eventDispatcher->dispatch($event, $event->getEventCode());

        $this->addFlash('success', $this->trans('spipu.user.success.confirm'));
        return $this->redirectToRoute('spipu_user_security_login');
    }

    #[Route(path: '/recovery', name: 'spipu_user_account_recover', methods: 'GET|POST')]
    public function passwordRecover(
        FormFactory $formFactory,
        RecoveryForm $recoveryForm,
        ModuleConfigurationInterface $moduleConfiguration,
        UserRepository $userRepository,
        MailManager $mailManager
    ): Response {
        if (!$moduleConfiguration->hasAllowPasswordRecovery()) {
            throw $this->createNotFoundException();
        }
        $manager = $formFactory->create($recoveryForm);
        $manager->setSubmitButton('spipu.user.action.recover');
        if ($manager->validate()) {
            $this->container->get('request_stack')->getSession()->getFlashBag()->clear();
            $redirect = $this->redirectToRoute('spipu_user_account_recovery_waiting');

            try {
                $email = $manager->getForm()['email']->getData();
                $user = $userRepository->findOneBy(['email' => $email]);
                if (!$user) {
                    return $redirect;
                }

                $mailManager->sendRecoveryEmail($user);

                $event = new UserEvent($user, 'recovery_asked');
                $this->eventDispatcher->dispatch($event, $event->getEventCode());
            } catch (Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
            return $this->redirectToRoute('spipu_user_account_recovery_waiting');
        }

        return $this->render('@SpipuUser/recover.html.twig', ['manager' => $manager]);
    }

    #[Route(path: '/recovery-waiting', name: 'spipu_user_account_recovery_waiting', methods: 'GET')]
    public function recoveryWaiting(ModuleConfigurationInterface $moduleConfiguration): Response
    {
        if (!$moduleConfiguration->hasAllowPasswordRecovery()) {
            throw $this->createNotFoundException();
        }

        return $this->render('@SpipuUser/recover-waiting.html.twig');
    }

    #[Route(path: '/new-password/{email}/{token}', name: 'spipu_user_account_recovery_confirm', methods: 'GET|POST')]
    public function recoveryConfirm(
        FormFactory $formFactory,
        NewPasswordForm $newPasswordForm,
        ModuleConfigurationInterface $moduleConfiguration,
        UserRepository $userRepository,
        UserTokenManager $userTokenManager,
        string $email,
        string $token
    ): Response {
        if (!$moduleConfiguration->hasAllowPasswordRecovery()) {
            throw $this->createNotFoundException();
        }

        $user = $userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('danger', $this->trans('spipu.user.error.confirm'));
            return $this->redirectToRoute('spipu_user_security_login');
        }

        if (!$userTokenManager->isValid($user, $token)) {
            $this->addFlash('danger', $this->trans('spipu.user.error.confirm'));
            return $this->redirectToRoute('spipu_user_security_login');
        }

        $event = new UserEvent($user, 'recovery_allow');
        $this->eventDispatcher->dispatch($event, $event->getEventCode());

        $manager = $formFactory->create($newPasswordForm);
        $manager->setResource($user);
        $manager->setSubmitButton('spipu.ui.action.update');
        if ($manager->validate()) {
            $user->setActive(true);
            $userTokenManager->reset($user);

            $event = new UserEvent($user, 'recovery_update');
            $this->eventDispatcher->dispatch($event, $event->getEventCode());

            return $this->redirectToRoute('spipu_user_security_login');
        }

        return $this->render('@SpipuUser/recover-confirm.html.twig', ['manager' => $manager]);
    }

    private function trans(string $message, array $params = []): string
    {
        return $this->container->get('translator')->trans($message, $params);
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            'translator',
        ];
    }
}
