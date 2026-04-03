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

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Spipu\UserBundle\Entity\UserInterface;

class UserTokenManager
{
    private EntityManagerInterface $entityManager;
    private string $keySecret;
    private UserConfiguration $userConfiguration;

    public function __construct(
        EntityManagerInterface $entityManager,
        string $keySecret,
        UserConfiguration $userConfiguration
    ) {
        $this->entityManager = $entityManager;
        $this->keySecret = $keySecret;
        $this->userConfiguration = $userConfiguration;
    }

    public function generate(UserInterface $user): string
    {
        $currentTime = new DateTime('NOW');
        $user->setTokenDate($currentTime);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->getCurrentToken($user);
    }

    public function isValid(UserInterface $user, string $token): bool
    {
        if (!$user->getTokenDate()) {
            return false;
        }

        if ($this->isExpired($user)) {
            return false;
        }

        return $this->getCurrentToken($user) === $token;
    }

    public function reset(UserInterface $user): void
    {
        $user->setTokenDate(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    private function isExpired(UserInterface $user): bool
    {
        $expirationHours = $this->userConfiguration->getSecurityTokenExpiration();
        $now = new DateTime('NOW');
        $diff = $now->getTimestamp() - $user->getTokenDate()->getTimestamp();

        return $diff > ($expirationHours * 3600);
    }

    private function getCurrentToken(UserInterface $user): string
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
