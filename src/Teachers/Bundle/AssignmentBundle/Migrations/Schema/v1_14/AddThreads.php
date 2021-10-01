<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_14;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddThreads implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->createTable('teachers_message_thread');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('assignment_id', 'integer', ['notnull' => false]);
        $table->addColumn('first_message_id', 'integer', ['notnull' => false]);
        $table->addColumn('latest_message_id', 'integer', ['notnull' => false]);
        $table->addColumn('sender_id', 'integer', ['notnull' => false]);
        $table->addColumn('recipient_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['sender_id'], 'thread_sender_idx', []);
        $table->addIndex(['organization_id'], 'thread_org_idx', []);

        if ($schema->hasTable('teachers_assignment_message')) {
            $table = $schema->getTable('teachers_assignment_message');
            $table->addColumn('thread_id', 'integer', ['notnull' => false]);
            $table->addIndex(['thread_id'], 'message_thread_idx', []);
        }
    }
}
