<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Model\EnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Teachers\Bundle\InvoiceBundle\Entity\Payment;

class UpdatePaymentStatusesData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(Payment::STATUS_ENUM_CLASS);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $priorityCounter = 1;
        foreach (Payment::getAvailableStatuses() as $id => $data) {
            $priority = $priorityCounter++;
            /** @var EnumValue $status */
            $status = $enumRepo->findOneBy([
                'id' => $id
            ]);
            if ($status) {
                continue;
            }
            $enumOption = $enumRepo->createEnumValue($data['name'], $priority, $data['is_default'], $id);
            $manager->persist($enumOption);
        }
        $status = $enumRepo->findOneBy([
            'id' => Payment::STATUS_CREATED
        ]);
        $status->setDefault(false);
        $manager->persist($status);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadPaymentStatusData::class
        ];
    }

}
