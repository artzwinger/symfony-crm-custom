<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_13;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddRecipient implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment_message')) {
            $table = $schema->getTable('teachers_assignment_message');
            if (!$table->hasColumn('recipient_id')) {
                $table->addColumn('recipient_id', Types::INTEGER, ['notnull' => false]);
                $table->addColumn('viewed_by_recipient', Types::BOOLEAN, ['notnull' => true, 'default' => 0]);
            }
        }
    }
}
