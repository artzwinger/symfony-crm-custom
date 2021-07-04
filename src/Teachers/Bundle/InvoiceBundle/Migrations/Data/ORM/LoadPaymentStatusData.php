<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

class LoadPaymentStatusData extends AbstractFixture
{
    const STATUS_ENUM_CLASS = 'payment_status';
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::STATUS_ENUM_CLASS);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $priority = 1;
        foreach (Payment::getAvailableStatuses() as $id => $data) {
            $enumOption = $enumRepo->createEnumValue($data['name'], $priority++, $data['is_default'], $id);
            $manager->persist($enumOption);
        }
        $manager->flush();
    }
}
