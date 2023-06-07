<?php
namespace Spipu\UserBundle\Tests\Unit\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Tests\GenericUser;
use Spipu\UserBundle\Tests\SpipuUserMock;

class UserTest extends TestCase
{
    public function testEntity()
    {
        $date = new DateTime();

        $entity = SpipuUserMock::getUserEntity(1);
        $this->assertInstanceOf(UserInterface::class, $entity);
        $this->assertSame(1, $entity->getId());

        $entity->setActive(false);
        $entity->setRoles([]);

        $this->assertSame(0, $entity->getNbLogin());
        $this->assertSame(0, $entity->getNbTryLogin());
        $this->assertSame(null, $entity->getSalt());
        $this->assertSame(false, $entity->getActive());
        $this->assertSame([], $entity->getRoles());

        $entity->setActive(true);
        $this->assertSame(true, $entity->getActive());
        $this->assertSame(['ROLE_USER'], $entity->getRoles());

        $entity->setEmail('mock_email');
        $entity->setUsername('mock_username');
        $entity->setPassword('mock_password');
        $entity->setPlainPassword('mock_plain_password');
        $entity->setFirstName('mock_first_name');
        $entity->setLastName('mock_last_name');
        $entity->setRoles(['ROLE_MOCK']);
        $entity->setNbLogin(10);
        $entity->setNbTryLogin(20);
        $entity->setActive(true);
        $entity->setTokenDate($date);

        $this->assertSame('mock_email', $entity->getEmail());
        $this->assertSame('mock_username', $entity->getUsername());
        $this->assertSame('mock_password', $entity->getPassword());
        $this->assertSame('mock_plain_password', $entity->getPlainPassword());
        $this->assertSame('mock_first_name', $entity->getFirstName());
        $this->assertSame('mock_last_name', $entity->getLastName());
        $this->assertSame(['ROLE_MOCK'], $entity->getRoles());
        $this->assertSame(10, $entity->getNbLogin());
        $this->assertSame(20, $entity->getNbTryLogin());
        $this->assertSame(true, $entity->getActive());
        $this->assertSame($date, $entity->getTokenDate());

        $entity->eraseCredentials();
        $this->assertSame(null, $entity->getPlainPassword());

        $serialized = $entity->serialize();

        $new = new GenericUser(null);
        $new->unserialize($serialized);

        $this->assertSame($serialized, $new->serialize());
        $this->assertSame($entity->getId(), $new->getId());
        $this->assertSame($entity->getEmail(), $new->getEmail());
        $this->assertSame($entity->getUsername(), $new->getUsername());
    }
}
