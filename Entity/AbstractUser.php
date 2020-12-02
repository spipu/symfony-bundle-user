<?php
declare(strict_types = 1);

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

    const DEFAULT_ROLE = 'ROLE_USER';

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
    public function getRoles(): ?array
    {
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
     * String representation of object
     * @return string
     */
    public function serialize(): string
    {
        return serialize(
            [
                $this->getId(),
                $this->getEmail(),
                $this->getUsername(),
                $this->getPassword(),
                $this->getFirstName(),
                $this->getLastName(),
                $this->getRoles(),
                $this->getNbLogin(),
                $this->getNbTryLogin(),
                $this->getActive(),
                $this->getCreatedAt(),
                $this->getUpdatedAt()
            ]
        );
    }

    /**
     * Constructs the object
     * @param string $serialized
     * @return void
     */
    public function unserialize($serialized): void  //@codingStandardsIgnoreLine
    {
        list(
            $this->id,
            $this->email,
            $this->username,
            $this->password,
            $this->firstName,
            $this->lastName,
            $this->roles,
            $this->nbLogin,
            $this->nbTryLogin,
            $this->active,
            $this->createdAt,
            $this->updatedAt
        ) = unserialize($serialized, ['allowed_classes' => false]);
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
}
