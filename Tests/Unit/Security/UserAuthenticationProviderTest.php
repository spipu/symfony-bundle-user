<?php
namespace Spipu\UserBundle\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Tests\SpipuUserMock;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\Security\UserAuthenticationProvider;
use Spipu\UserBundle\Security\UserChecker;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class UserAuthenticationProviderTest extends TestCase
{
    public function testOk()
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setUsername('mock_username')
            ->setPassword('encoded_good_password')
            ->setActive(true)
            ->setNbLogin(9)
            ->setNbTryLogin(3)
            ->setTokenDate(new \DateTime());

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->once())->method('persist')->with($user);
        $entityManager->expects($this->once())->method('flush');

        $token = new UsernamePasswordToken('mock_username', 'good_password', 'user', []);

        $service = new UserAuthenticationProvider(
            SymfonyMock::getUserProvider($this, $user),
            new UserChecker(),
            'user',
            SymfonyMock::getEncoderFactory($this),
            false,
            $entityManager
        );

        $service->authenticate($token);

        $this->assertSame(null, $user->getTokenDate());
        $this->assertSame(10, $user->getNbLogin());
        $this->assertSame(0, $user->getNbTryLogin());
    }

    public function testKo()
    {
        $user = SpipuUserMock::getUserEntity(42);
        $user
            ->setUsername('mock_username')
            ->setPassword('encoded_good_password')
            ->setActive(true)
            ->setNbLogin(9)
            ->setNbTryLogin(3)
            ->setTokenDate(new \DateTime());

        $entityManager = SymfonyMock::getEntityManager($this);
        $entityManager->expects($this->once())->method('persist')->with($user);
        $entityManager->expects($this->once())->method('flush');

        $token = new UsernamePasswordToken('mock_username', 'bad_password', 'user', []);

        $service = new UserAuthenticationProvider(
            SymfonyMock::getUserProvider($this, $user),
            new UserChecker(),
            'user',
            SymfonyMock::getEncoderFactory($this),
            false,
            $entityManager
        );

        $this->expectException(BadCredentialsException::class);
        try {
            $service->authenticate($token);
        } finally {
            $this->assertInstanceOf(\DateTimeInterface::class, $user->getTokenDate());
            $this->assertSame(9, $user->getNbLogin());
            $this->assertSame(4, $user->getNbTryLogin());
        }
    }
}