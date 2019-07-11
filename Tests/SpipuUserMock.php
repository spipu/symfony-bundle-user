<?php
namespace Spipu\UserBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\Service\Filesystem;
use Spipu\CoreBundle\Service\FinderFactory;
use Spipu\CoreBundle\Service\MailManager;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Service\UserTokenManager;
use Symfony\Component\Finder\Finder;

class SpipuUserMock extends TestCase
{
    public static function getUserEntity(int $id = null)
    {
        $entity = new GenericUser();

        if ($id !== null) {
            $setId = \Closure::bind(
                function ($value) {
                    $this->id = $value;
                },
                $entity,
                $entity
            );
            $setId($id);
        }

        return $entity;
    }

    /**
     * @param TestCase $testsCase
     * @return MockObject|UserTokenManager
     */
    public static function getUserTokenManager(TestCase $testsCase)
    {
        $userTokenManager = $testsCase->createMock(UserTokenManager::class);

        $userTokenManager
            ->method('generate')
            ->willReturnCallback(
                function (GenericUser $user) {
                    $user->setTokenDate(new \DateTime());
                    return ('mock_token_' . $user->getId());
                }
            );

        $userTokenManager
            ->method('isValid')
            ->willReturnCallback(
                function (GenericUser $user, string $token) {
                    if (!$user->getTokenDate()) {
                        return false;
                    }
                    return ('mock_token_' . $user->getId()) === $token;
                }
            );

        $userTokenManager
            ->method('reset')
            ->willReturnCallback(
                function (GenericUser $user) {
                    $user->setTokenDate(null);
                }
            );

        /** @var UserTokenManager $userTokenManager */
        return $userTokenManager;
    }
}
