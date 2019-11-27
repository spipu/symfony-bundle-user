<?php
namespace Spipu\UserBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Spipu\UserBundle\Repository\UserRepository;
use Spipu\UserBundle\Service\ModuleConfiguration;
use Spipu\UserBundle\Service\ModuleConfigurationInterface;
use Spipu\UserBundle\Tests\GenericUser;

class ModuleConfigurationTest extends TestCase
{
    /**
     * @param TestCase $testCase
     * @param bool $creation
     * @param bool $password
     * @return ModuleConfiguration
     */
    public static function getService(TestCase $testCase, bool $creation, bool $password)
    {
        /** @var UserRepository $repository */
        $moduleConfiguration = new ModuleConfiguration(
            'MockUser',
            GenericUser::class,
            $creation,
            $password
        );

        return $moduleConfiguration;
    }

    public function testService()
    {
        $moduleConfiguration = self::getService($this, true, false);

        $this->assertInstanceOf(ModuleConfigurationInterface::class, $moduleConfiguration);
        $this->assertSame('MockUser', $moduleConfiguration->getEntityName());
        $this->assertInstanceOf(GenericUser::class, $moduleConfiguration->getNewEntity());
        $this->assertSame(true, $moduleConfiguration->hasAllowAccountCreation());
        $this->assertSame(false, $moduleConfiguration->hasAllowPasswordRecovery());
    }
}
