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

use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'spipu_user_security_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        ModuleConfigurationInterface $moduleConfiguration
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('danger', $error->getMessageKey());
        }

        return $this->render(
            '@SpipuUser/login.html.twig',
            [
                'lastUsername' => $authenticationUtils->getLastUsername(),
                'can' => [
                    'accountCreation'  => $moduleConfiguration->hasAllowAccountCreation(),
                    'passwordRecovery' => $moduleConfiguration->hasAllowPasswordRecovery(),
                ]
            ]
        );
    }

    #[Route(path: '/logout', name: 'spipu_user_security_logout')]
    public function logout(): void
    {
    }
}
