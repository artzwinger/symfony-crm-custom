<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_15;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class FixDateColumns implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_message_thread')) {
            $table = $schema->getTable('teachers_message_thread');
            $table->dropColumn('createdat');
            $table->dropColumn('updatedat');
            $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)', 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)', 'default' => 'CURRENT_TIMESTAMP']);
        }
        if ($schema->hasTable('teachers_assignment_message')) {
            $table = $schema->getTable('teachers_assignment_message');
            $table->dropColumn('createdat');
            $table->dropColumn('updatedat');
            $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)', 'default' => 'CURRENT_TIMESTAMP']);
            $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)', 'default' => 'CURRENT_TIMESTAMP']);
        }
    }
}
