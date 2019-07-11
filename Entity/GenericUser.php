<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Spipu\UiBundle\Entity\EntityInterface;
use Spipu\UiBundle\Entity\TimestampableTrait;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="username", message="Username already taken")
 * @UniqueEntity(fields="email", message="Email already taken")
 */
class GenericUser implements EntityInterface, UserInterface, \Serializable
{
    use TimestampableTrait;

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
    private $roles = ['ROLE_USER'];

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
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $tokenDate = null;

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
     * @return User
     */
    public function setEmail(string $email): self
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
     * @return User
     */
    public function setUsername(string $username): self
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
     * @return User
     */
    public function setPassword(string $password): self
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
     * @return self
     */
    public function setPlainPassword(string $password): self
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
     * @return User
     */
    public function setFirstName(string $firstName): self
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
     * @return User
     */
    public function setLastName(string $lastName): self
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
            return ['ROLE_USER'];
        }

        return $this->roles;
    }

    /**
     * Set the roles
     * @param string[] $roles
     * @return User
     */
    public function setRoles(array $roles): self
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
     * @return User
     */
    public function setNbLogin(int $nbLogin): self
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
     * @return User
     */
    public function setNbTryLogin(int $nbTryLogin): self
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
     * @return self
     */
    public function eraseCredentials(): self
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
     * @return User
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return ?\DateTime
     */
    public function getTokenDate(): ?\DateTime
    {
        return $this->tokenDate;
    }

    /**
     * @param ?\DateTime $tokenDate
     * @return User
     */
    public function setTokenDate(?\DateTime $tokenDate): self
    {
        $this->tokenDate = $tokenDate;

        return $this;
    }
}
