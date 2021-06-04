<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddStudent implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');
            if (!$table->hasColumn('student_id')) {
                $table->addColumn('student_id', 'integer', ['notnull' => false]);
            }
        }
    }
}
