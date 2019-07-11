<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="spipu_user")
 * @ORM\Entity(repositoryClass="Spipu\UserBundle\Repository\UserRepository")
 */
class User extends GenericUser
{
}
