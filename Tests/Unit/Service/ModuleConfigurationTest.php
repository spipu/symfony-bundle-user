<?php
namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Entity\GenericUser;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\ModuleConfiguration;

class ModuleConfigurationTest extends TestCase
{
    /**
     * @param TestCase $testCase
     * @return ModuleConfiguration
     */
    public static function getService(TestCase $testCase, bool $creation, bool $password)
    {
        $repository = $testCase->createMock(UserRepository::class);

        /** @var UserRepository $repository */
        $moduleConfiguration = new ModuleConfiguration(
            'MockUser',
            GenericUser::class,
            $repository,
            $creation,
            $password
        );

        return $moduleConfiguration;
    }

    public function testService()
    {
        $moduleConfiguration = self::getService($this, true, false);

        $this->assertSame('MockUser', $moduleConfiguration->getEntityName());
        $this->assertInstanceOf(GenericUser::class, $moduleConfiguration->getNewEntity());
        $this->assertInstanceOf(UserRepository::class, $moduleConfiguration->getRepository());
        $this->assertSame(true, $moduleConfiguration->hasAllowAccountCreation());
        $this->assertSame(false, $moduleConfiguration->hasAllowPasswordRecovery());
    }
}
