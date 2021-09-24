<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_12;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddDenialReason implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment_message')) {
            $table = $schema->getTable('teachers_assignment_message');
            if (!$table->hasColumn('denial_reason')) {
                $table->addColumn('denial_reason', 'text', ['notnull' => false]);
            }
        }
    }
}
