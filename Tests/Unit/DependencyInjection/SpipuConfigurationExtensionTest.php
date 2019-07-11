<?php
namespace Spipu\UserBundle\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Spipu\CoreBundle\DependencyInjection\RolesHierarchiExtensionExtensionInterface;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\CoreBundle\Tests\SymfonyMock;
use Spipu\UserBundle\DependencyInjection\SpipuUserExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class SpipuUserExtensionTest extends TestCase
{
    public function testBase()
    {
        $builder = SymfonyMock::getContainerBuilder($this);

        $extension = new SpipuUserExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);

        $extension->load([], $builder);

        $this->assertInstanceOf(RolesHierarchiExtensionExtensionInterface::class, $extension);
        $this->assertInstanceOf(RoleDefinitionInterface::class, $extension->getRolesHierarchy());
    }
}