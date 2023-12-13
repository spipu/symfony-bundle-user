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

namespace Spipu\UserBundle\Entity;

use DateTimeInterface;
use Serializable;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\TimestampableInterface;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

interface UserInterface extends
    EntityInterface,
    BaseUserInterface,
    TimestampableInterface,
    Serializable,
    PasswordAuthenticatedUserInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;

    public function setEmail(string $email): self;

    public function getUsername(): string;

    public function getUserIdentifier(): string;

    public function setUsername(string $username): self;

    public function setPassword(string $password): self;

    public function getPlainPassword(): ?string;

    public function setPlainPassword(string $password): self;

    public function getFirstName(): ?string;

    public function setFirstName(string $firstName): self;

    public function getLastName(): ?string;

    public function setLastName(string $lastName): self;

    /**
     * @param string[] $roles
     * @return self
     */
    public function setRoles(array $roles): self;

    public function getNbLogin(): ?int;

    public function setNbLogin(int $nbLogin): self;

    public function getNbTryLogin(): ?int;

    public function setNbTryLogin(int $nbTryLogin): self;

    public function getActive(): ?bool;

    public function setActive(bool $active): self;

    public function getTokenDate(): ?DateTimeInterface;

    public function setTokenDate(?DateTimeInterface $tokenDate): self;

    public function getPasswordDate(): ?DateTimeInterface;

    public function setPasswordDate(?DateTimeInterface $passwordDate): self;
}
