<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spipu\UserBundle\Tests;

use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Entity\AbstractUser;
use Spipu\UserBundle\Entity\UserInterface;
use Spipu\UserBundle\Service\UserTokenManager;

class SpipuUserMock extends TestCase
{
    /**
     * @param int|null $id
     * @return UserInterface
     */
    public static function getUserEntity(int $id = null)
    {
        return new GenericUser($id);
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
                function (UserInterface $user) {
                    $user->setTokenDate(new DateTime());
                    return ('mock_token_' . $user->getId());
                }
            );

        $userTokenManager
            ->method('isValid')
            ->willReturnCallback(
                function (UserInterface $user, string $token) {
                    if (!$user->getTokenDate()) {
                        return false;
                    }
                    return ('mock_token_' . $user->getId()) === $token;
                }
            );

        $userTokenManager
            ->method('reset')
            ->willReturnCallback(
                function (UserInterface $user) {
                    $user->setTokenDate(null);
                }
            );

        /** @var UserTokenManager $userTokenManager */
        return $userTokenManager;
    }
}

class GenericUser extends AbstractUser implements UserInterface
{
    private ?int $id;

    /**
     * GenericUser constructor.
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        if ($this->id !== null) {
            return $this->id;
        }

        return parent::getId();
    }
}
