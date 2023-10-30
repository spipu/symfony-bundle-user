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
use Doctrine\ORM\Mapping as ORM;
use Spipu\UiBundle\Entity\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: "username", message: "Username already taken")]
#[UniqueEntity(fields: "email", message: "Email already taken")]
abstract class AbstractUser implements UserInterface
{
    use TimestampableTrait;

    public const DEFAULT_ROLE = 'ROLE_USER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 4)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $password = null;

    #[Assert\Length(max: 4096)]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $lastName = null;

    /** @var string[] */
    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column]
    private int $nbLogin = 0;

    #[ORM\Column]
    private int $nbTryLogin = 0;

    #[ORM\Column]
    private bool $active = false;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTimeInterface $tokenDate = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTimeInterface $passwordDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->getUsername();
    }

    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): UserInterface
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): UserInterface
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): UserInterface
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
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
     * @param string[] $roles
     * @return UserInterface
     */
    public function setRoles(array $roles): UserInterface
    {
        $this->roles = $roles;

        return $this;
    }
    public function getNbLogin(): ?int
    {
        return $this->nbLogin;
    }

    public function setNbLogin(int $nbLogin): UserInterface
    {
        $this->nbLogin = $nbLogin;

        return $this;
    }

    public function getNbTryLogin(): ?int
    {
        return $this->nbTryLogin;
    }

    public function setNbTryLogin(int $nbTryLogin): UserInterface
    {
        $this->nbTryLogin = $nbTryLogin;

        return $this;
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

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

    public function unserialize(string $data): void
    {
        $this->__unserialize(
            unserialize($data, ['allowed_classes' => false])
        );
    }

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

    public function getSalt(): ?string
    {
        return  null;
    }

    public function eraseCredentials(): UserInterface
    {
        $this->plainPassword = null;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): UserInterface
    {
        $this->active = $active;

        return $this;
    }

    public function getTokenDate(): ?DateTimeInterface
    {
        return $this->tokenDate;
    }

    public function setTokenDate(?DateTimeInterface $tokenDate): UserInterface
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }

    public function getPasswordDate(): ?DateTimeInterface
    {
        return $this->passwordDate;
    }

    public function setPasswordDate(?DateTimeInterface $passwordDate): UserInterface
    {
        $this->passwordDate = $passwordDate;

        return $this;
    }
}
