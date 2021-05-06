<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateAssignmentColumns implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment')) {
            $table = $schema->getTable('teachers_assignment');

            $table->dropColumn('subject');

            $table->addColumn('first_name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('last_name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('course_name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('course_prefixes', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('work_today', Types::BOOLEAN, ['notnull' => true]);
            $table->addColumn('due_date', 'datetime', ['notnull' => true]);
            $table->addColumn('course_url', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('instructions', 'text', ['notnull' => false]);
        }
    }
}
