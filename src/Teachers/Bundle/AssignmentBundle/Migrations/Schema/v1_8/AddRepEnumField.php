<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class AddRepEnumField implements Migration, ExtendExtensionAwareInterface
{
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment')) {
            $enumTable = $this->extendExtension->addEnumField(
                $schema,
                'teachers_assignment',
                'rep',
                'assignment_rep'
            );

            $options = new OroOptions();
            $options->set('enum', 'immutable_codes', array_keys(Assignment::getAvailableReps()));
            $options->set('extend', 'owner', ExtendScope::OWNER_SYSTEM);

            $enumTable->addOption(OroOptions::KEY, $options);
        }
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
