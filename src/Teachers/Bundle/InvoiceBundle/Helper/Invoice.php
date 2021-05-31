<?php

namespace Teachers\Bundle\InvoiceBundle\Helper;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Manager\EmailTemplateManager;
use Oro\Bundle\EmailBundle\Model\EmailTemplateCriteria;
use Oro\Bundle\EmailBundle\Model\From;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice as InvoiceEntity;

class Invoice
{
    const INVOICE_CREATED_TEMPLATE = 'teachers_invoice_created';
    /**
     * @var EmailTemplateManager
     */
    private $emailTemplateManager;
    /**
     * @var ConfigManager
     */
    private $configManager;

    /**
     * @param EmailTemplateManager $emailTemplateManager
     * @param ConfigManager $configManager
     */
    public function __construct(EmailTemplateManager $emailTemplateManager, ConfigManager $configManager)
    {
        $this->emailTemplateManager = $emailTemplateManager;
        $this->configManager = $configManager;
    }

    public function sendEmailForInvoice(InvoiceEntity $invoice): int
    {
        $senderEmail = $this->configManager->get('oro_notification.email_notification_sender_email');
        $senderName = $this->configManager->get('oro_notification.email_notification_sender_name');
        return $this->emailTemplateManager->sendTemplateEmail(
            From::emailAddress($senderEmail, $senderName),
            [$invoice->getStudent()],
            new EmailTemplateCriteria(self::INVOICE_CREATED_TEMPLATE, 'Teachers\Bundle\InvoiceBundle\Entity\Invoice'),
            ['invoice' => $invoice, 'assignment' => $invoice->getAssignment()]
        );
    }
}
