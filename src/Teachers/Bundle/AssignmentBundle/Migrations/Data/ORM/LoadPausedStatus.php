<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Model\EnumValue;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class LoadPausedStatus extends AbstractFixture
{
    const STATUS_ENUM_CLASS = 'assignment_status';

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::STATUS_ENUM_CLASS);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        /** @var EnumValue $status */
        $status = $enumRepo->findOneBy([
            'id' => Assignment::STATUS_PAUSED_DUE_NONPAYMENT
        ]);
        if ($status) {
            return;
        }
        $enumOption = $enumRepo->createEnumValue(
            'Paused Due to Nonpayment',
            count(Assignment::getAvailableStatuses()),
            false,
            Assignment::STATUS_PAUSED_DUE_NONPAYMENT
        );
        $manager->persist($enumOption);
        $manager->flush();
    }
}
