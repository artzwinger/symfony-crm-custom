<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

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
        if ($schema->hasTable('teachers_application')) {
            $enumTable = $this->extendExtension->addEnumField(
                $schema,
                'teachers_application',
                'rep',
                'application_rep'
            );

            $options = new OroOptions();
            $options->set('enum', 'immutable_codes', array_keys(Application::getAvailableReps()));
            $options->set('extend', 'owner', ExtendScope::OWNER_SYSTEM);

            $enumTable->addOption(OroOptions::KEY, $options);
        }
    }

    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }
}
