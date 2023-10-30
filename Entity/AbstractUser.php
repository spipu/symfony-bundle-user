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
use Doctrine\ORM\Mapping as ORM;
use Spipu\UiBundle\Entity\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="username", message="Username already taken")
 * @UniqueEntity(fields="email", message="Email already taken")
 */
abstract class AbstractUser implements UserInterface
{
    use TimestampableTrait;

    public const DEFAULT_ROLE = 'ROLE_USER';

    /**
     * @var int|null
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(min = 4)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var string
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $lastName;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $nbLogin = 0;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $nbTryLogin = 0;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = false;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenDate = null;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordDate = null;

    /**
     * Get the PK id
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the email
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the email
     * @param string $email
     * @return UserInterface
     */
    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the username
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return string|null
     */
    public function getUserIdentifier(): ?string
    {
        return $this->getUsername();
    }

    /**
     * Set the username
     * @param string $username
     * @return UserInterface
     */
    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the password
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the password
     * @param string $password
     * @return UserInterface
     */
    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the PlainPassword
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set the PlainPassword
     * @param string $password
     * @return UserInterface
     */
    public function setPlainPassword(string $password): UserInterface
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * Get the FirstName
     * @return null|string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Set the FirstName
     * @param string $firstName
     * @return UserInterface
     */
    public function setFirstName(string $firstName): UserInterface
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get the LastName
     * @return null|string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Set the LastName
     * @param string $lastName
     * @return UserInterface
     */
    public function setLastName(string $lastName): UserInterface
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get the roles
     * @return string[]
     */
    public function getRoles(): array
    {
        if (!$this->active) {
            return [];
        }

        if (empty($this->roles)) {
            return [static::DEFAULT_ROLE];
        }

        return $this->roles;
    }

    /**
     * Set the roles
     * @param string[] $roles
     * @return UserInterface
     */
    public function setRoles(array $roles): UserInterface
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the Nb of Login
     * @return int|null
     */
    public function getNbLogin(): ?int
    {
        return $this->nbLogin;
    }

    /**
     * Set the Nb of Login
     * @param int $nbLogin
     * @return UserInterface
     */
    public function setNbLogin(int $nbLogin): UserInterface
    {
        $this->nbLogin = $nbLogin;

        return $this;
    }

    /**
     * Get the nb of try login with a wrong password
     * @return int|null
     */
    public function getNbTryLogin(): ?int
    {
        return $this->nbTryLogin;
    }

    /**
     * Get the nb of try login with a wrong password
     * @param int $nbTryLogin
     * @return UserInterface
     */
    public function setNbTryLogin(int $nbTryLogin): UserInterface
    {
        $this->nbTryLogin = $nbTryLogin;

        return $this;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'id'         => $this->getId(),
            'email'      => $this->email,
            'username'   => $this->username,
            'password'   => $this->password,
            'firstName'  => $this->firstName,
            'lastName'   => $this->lastName,
            'roles'      => $this->roles,
            'nbLogin'    => $this->nbLogin,
            'nbTryLogin' => $this->nbTryLogin,
            'active'     => $this->active,
            'createdAt'  => $this->createdAt,
            'updatedAt'  => $this->updatedAt,
        ];
    }

    /**
     * @param string $data
     * @return void
     */
    public function unserialize($data): void  //@codingStandardsIgnoreLine
    {
        $this->__unserialize(
            unserialize($data, ['allowed_classes' => false])
        );
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->id           = $data['id'];
        $this->email        = $data['email'];
        $this->username     = $data['username'];
        $this->password     = $data['password'];
        $this->firstName    = $data['firstName'];
        $this->lastName     = $data['lastName'];
        $this->roles        = $data['roles'];
        $this->nbLogin      = $data['nbLogin'];
        $this->nbTryLogin   = $data['nbTryLogin'];
        $this->active       = $data['active'];
        $this->createdAt    = $data['createdAt'];
        $this->updatedAt    = $data['updatedAt'];
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * @return string|null
     */
    public function getSalt(): ?string
    {
        return  null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * @return UserInterface
     */
    public function eraseCredentials(): UserInterface
    {
        $this->plainPassword = null;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return UserInterface
     */
    public function setActive(bool $active): UserInterface
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return DateTime|null ?\DateTime
     */
    public function getTokenDate(): ?DateTime
    {
        return $this->tokenDate;
    }

    /**
     * @param DateTime|null $tokenDate
     * @return UserInterface
     */
    public function setTokenDate(?DateTime $tokenDate): UserInterface
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    /**
     * @return DateTime|null ?\DateTime
     */
    public function getPasswordDate(): ?DateTime
    {
        return $this->passwordDate;
    }

    /**
     * @param DateTime|null $passwordDate
     * @return UserInterface
     */
    public function setPasswordDate(?DateTime $passwordDate): UserInterface
    {
        $this->passwordDate = $passwordDate;

        return $this;
    }
}
