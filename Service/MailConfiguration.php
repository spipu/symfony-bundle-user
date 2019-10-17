<?php
declare(strict_types = 1);

namespace Spipu\UserBundle\Service;

class MailConfiguration implements MailConfigurationInterface
{
    /**
     * @return string
     */
    public function getEmailFrom(): string
    {
        return 'no-reply@mysite.fr';
    }
}
