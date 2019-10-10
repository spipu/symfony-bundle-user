<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Spipu\UserBundle\Entity\GenericUser;

class UserTokenManager
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $keySecret;

    /**
     * UserTokenService constructor.
     * @param EntityManagerInterface $entityManager
     * @param string $keySecret
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        string $keySecret
    ) {
        $this->entityManager = $entityManager;
        $this->keySecret = $keySecret;
    }

    /**
     * @param GenericUser $user
     * @return string
     */
    public function generate(GenericUser $user): string
    {
        $currentTime = new DateTime('NOW');
        $user->setTokenDate($currentTime);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->getCurrentToken($user);
    }

    /**
     * @param GenericUser $user
     * @param string $token
     * @return bool
     */
    public function isValid(GenericUser $user, string $token): bool
    {
        if (!$user->getTokenDate()) {
            return false;
        }

        return $this->getCurrentToken($user) === $token;
    }

    /**
     * @param GenericUser $user
     * @return void
     */
    public function reset(GenericUser $user): void
    {
        $user->setTokenDate(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param GenericUser $user
     * @return string
     */
    private function getCurrentToken(GenericUser $user): string
    {
        $data = [
            $user->getId(),
            $user->getEmail(),
            $user->getUsername(),
            $user->getCreatedAt()->format('YmdHis'),
            $user->getTokenDate()->format('YmdHis'),
            $this->keySecret
        ];

        return hash('sha256', implode('-', $data));
    }
}
