<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddAssignmentField implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');
            if (!$table->hasColumn('assignment_id')) {
                $table->addColumn('assignment_id', Types::INTEGER, ['notnull' => false]);
                $table->addIndex(['assignment_id'], 'teachers_application_asmg_id', []);
            }
        }
    }
}
