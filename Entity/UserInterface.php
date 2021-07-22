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

use DateTime;
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
    /**
     * Get the PK id
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get the email
     * @return null|string
     */
    public function getEmail(): ?string;

    /**
     * Set the email
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self;

    /**
     * Temporary definition, will be removed in SF6 version, when the UserInterface will be clean
     * @return string|null
     */
    public function getPassword(): ?string;

    /**
     * Temporary definition, will be fixed (strict) in SF6 version, when the UserInterface will be clean
     * @return string
     */
    public function getUsername();

    /**
     * Set the username
     * @param string $username
     * @return self
     */
    public function setUsername(string $username): self;

    /**
     * Set the password
     * @param string $password
     * @return self
     */
    public function setPassword(string $password): self;

    /**
     * Get the PlainPassword
     * @return string|null
     */
    public function getPlainPassword(): ?string;

    /**
     * Set the PlainPassword
     * @param string $password
     * @return self
     */
    public function setPlainPassword(string $password): self;

    /**
     * Get the FirstName
     * @return null|string
     */
    public function getFirstName(): ?string;

    /**
     * Set the FirstName
     * @param string $firstName
     * @return self
     */
    public function setFirstName(string $firstName): self;

    /**
     * Get the LastName
     * @return null|string
     */
    public function getLastName(): ?string;

    /**
     * Set the LastName
     * @param string $lastName
     * @return self
     */
    public function setLastName(string $lastName): self;

    /**
     * Set the roles
     * @param string[] $roles
     * @return self
     */
    public function setRoles(array $roles): self;

    /**
     * Get the Nb of Login
     * @return int|null
     */
    public function getNbLogin(): ?int;

    /**
     * Set the Nb of Login
     * @param int $nbLogin
     * @return self
     */
    public function setNbLogin(int $nbLogin): self;

    /**
     * Get the nb of try login with a wrong password
     * @return int|null
     */
    public function getNbTryLogin(): ?int;

    /**
     * Get the nb of try login with a wrong password
     * @param int $nbTryLogin
     * @return self
     */
    public function setNbTryLogin(int $nbTryLogin): self;

    /**
     * @return bool|null
     */
    public function getActive(): ?bool;

    /**
     * @param bool $active
     * @return self
     */
    public function setActive(bool $active): self;

    /**
     * @return DateTime|null ?\DateTime
     */
    public function getTokenDate(): ?DateTime;

    /**
     * @param DateTime|null $tokenDate
     * @return self
     */
    public function setTokenDate(?DateTime $tokenDate): self;

    /**
     * @return string|null
     */
    public function getUserIdentifier(): ?string;
}
