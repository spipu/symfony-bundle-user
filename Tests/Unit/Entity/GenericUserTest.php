<?php
namespace Spipu\UserBundle\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\UserBundle\Entity\GenericUser;

class GenericUserTest extends TestCase
{
    public function testEntity()
    {
        $date = new \DateTime();

        $entity = SpipuUserMock::getUserEntity(1);
        $this->assertSame(1, $entity->getId());

        $entity->setRoles([]);

        $this->assertSame(['ROLE_USER'], $entity->getRoles());
        $this->assertSame(0, $entity->getNbLogin());
        $this->assertSame(0, $entity->getNbTryLogin());
        $this->assertSame(false, $entity->getActive());
        $this->assertSame(null, $entity->getSalt());

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

        $new = new GenericUser();
        $new->unserialize($serialized);

        $this->assertSame($serialized, $new->serialize());
        $this->assertSame($entity->getId(), $new->getId());
        $this->assertSame($entity->getEmail(), $new->getEmail());
        $this->assertSame($entity->getUsername(), $new->getUsername());
    }
}
