<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddClassStartDateField implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment')) {
            $table = $schema->getTable('teachers_assignment');
            if (!$table->hasColumn('class_start_date')) {
                $table->addColumn('class_start_date', 'datetime', ['notnull' => false]);
            }
        }
    }
}
