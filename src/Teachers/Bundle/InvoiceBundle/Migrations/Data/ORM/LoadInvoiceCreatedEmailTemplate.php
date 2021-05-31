<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Data\ORM;

use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;

class LoadInvoiceCreatedEmailTemplate extends AbstractEmailFixture
{
    /**
     * Return path to email templates
     *
     * @return string
     */
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@TeachersInvoiceBundle/Migrations/Data/ORM/emails/invoice');
    }
}
