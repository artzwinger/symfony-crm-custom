<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_9;

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
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');
            if (!$table->hasColumn('class_start_date')) {
                $table->addColumn('class_start_date', 'datetime', ['notnull' => false]);
            }
        }
    }
}
