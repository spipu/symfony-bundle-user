<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Form\Options;

class AdminRole extends AbstractRole
{
    /**
     * @return string
     */
    protected function getPurpose(): string
    {
        return 'admin';
    }
}
