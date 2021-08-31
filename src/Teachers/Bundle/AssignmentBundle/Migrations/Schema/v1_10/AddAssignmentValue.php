<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_10;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddAssignmentValue implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment')) {
            $table = $schema->getTable('teachers_assignment');
            if (!$table->hasColumn('assignment_value')) {
                $table->addColumn('assignment_value', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => false]);
            }
        }
    }
}
