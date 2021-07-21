<?php
declare(strict_types=1);

namespace Spipu\UserBundle\Service;

interface MailConfigurationInterface
{
    /**
     * @return string
     */
    public function getEmailFrom(): string;
}
