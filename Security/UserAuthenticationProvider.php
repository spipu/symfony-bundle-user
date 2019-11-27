<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Spipu\UserBundle\Entity\UserInterface as SpipuUserInterface;
use Symfony\Component\Security\Core\Authentication\Provider\DaoAuthenticationProvider;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserAuthenticationProvider extends DaoAuthenticationProvider
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PHP constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param UserCheckerInterface $userChecker
     * @param string $providerKey
     * @param EncoderFactoryInterface $encoderFactory
     * @param bool $hideUserNotFoundExceptions
     * @param EntityManagerInterface $entityManager
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function __construct(
        UserProviderInterface $userProvider,
        UserCheckerInterface $userChecker,
        string $providerKey,
        EncoderFactoryInterface $encoderFactory,
        bool $hideUserNotFoundExceptions = true,
        EntityManagerInterface $entityManager = null
    ) {
        parent::__construct($userProvider, $userChecker, $providerKey, $encoderFactory, $hideUserNotFoundExceptions);

        $this->entityManager = $entityManager;
    }

    /**
     * @param UserInterface $user
     * @param UsernamePasswordToken $token
     * @return void
     */
    protected function checkAuthentication(UserInterface $user, UsernamePasswordToken $token)
    {
        /** @var SpipuUserInterface $user */
        try {
            parent::checkAuthentication($user, $token);

            $user->setTokenDate(null);
            $user->setNbTryLogin(0);
            $user->setNbLogin($user->getNbLogin() + 1);
        } catch (BadCredentialsException $e) {
            $user->setNbTryLogin($user->getNbTryLogin() + 1);
            throw $e;
        } finally {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
