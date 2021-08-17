<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class LoadRepData extends AbstractFixture
{
    const REP_ENUM_CLASS = 'assignment_rep';
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $className = ExtendHelper::buildEnumValueClassName(self::REP_ENUM_CLASS);
        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $manager->getRepository($className);
        $priority = 1;
        foreach (Assignment::getAvailableReps() as $id => $data) {
            $enumOption = $enumRepo->createEnumValue($data['name'], $priority++, $data['is_default'], $id);
            $manager->persist($enumOption);
        }
        $manager->flush();
    }
}
