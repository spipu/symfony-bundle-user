<?php
namespace Spipu\UserBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\RolesHierarchyBundleInterface;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\SpipuUserBundle;
use Symfony\Component\DependencyInjection\Extension\ConfigurableExtensionInterface;

class SpipuUserBundleTest extends TestCase
{
    public function testBase()
    {
        $builder = SymfonyMock::getContainerBuilder($this);
        $configurator = SymfonyMock::getContainerConfigurator($this);

        $bundle = new SpipuUserBundle();

        $this->assertInstanceOf(ConfigurableExtensionInterface::class, $bundle);

        $bundle->loadExtension([], $configurator, $builder);

        $this->assertInstanceOf(RolesHierarchyBundleInterface::class, $bundle);
        $this->assertInstanceOf(RoleDefinitionInterface::class, $bundle->getRolesHierarchy());
    }
}
