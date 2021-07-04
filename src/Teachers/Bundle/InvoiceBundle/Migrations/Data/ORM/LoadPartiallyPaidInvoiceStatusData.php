<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

class LoadPartiallyPaidInvoiceStatusData extends AbstractFixture
{
    const STATUS_ENUM_CLASS = 'invoice_status';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::STATUS_ENUM_CLASS);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $id = Invoice::STATUS_PARTIALLY_PAID;
        $data = [
            'name' => 'Partially Paid',
            'is_default' => false
        ];
        $enumOption = $enumRepo->createEnumValue($data['name'], count(Invoice::getAvailableStatuses()), $data['is_default'], $id);
        $manager->persist($enumOption);
        $manager->flush();
    }
}
