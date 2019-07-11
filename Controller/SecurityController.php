<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Controller;

use Spipu\UserBundle\Service\ModuleConfiguration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 */
class SecurityController extends AbstractController
{
    /**
     * @Route(
     *     "/login",
     *     name="spipu_user_security_login"
     * )
     * @param AuthenticationUtils $authenticationUtils
     * @param ModuleConfiguration $moduleConfiguration
     * @return Response
     */
    public function login(
        AuthenticationUtils $authenticationUtils,
        ModuleConfiguration $moduleConfiguration
    ): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('danger', $error->getMessage());
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

    /**
     * @Route(
     *     "/logout",
     *     name="spipu_user_security_logout"
     * )
     * @return void
     */
    public function logout(): void
    {
    }
}
