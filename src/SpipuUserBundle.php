<?php

/**
 * This file is part of a Spipu Bundle
 *
 * (c) Laurent Minguet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spipu\UserBundle;

use Spipu\CoreBundle\AbstractBundle;
use Spipu\CoreBundle\Service\RoleDefinitionInterface;
use Spipu\UserBundle\Service\RoleDefinition;

class SpipuUserBundle extends AbstractBundle
{
    public function getRolesHierarchy(): RoleDefinitionInterface
    {
        return new RoleDefinition();
    }
}
