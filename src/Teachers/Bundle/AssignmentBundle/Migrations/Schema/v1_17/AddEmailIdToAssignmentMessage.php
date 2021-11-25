<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_17;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddEmailIdToAssignmentMessage implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('teachers_assignment_message');
        $table->addColumn('email_imap_id', Types::INTEGER, ['notnull' => false]);
        $table->addIndex(['email_imap_id'], 'msg_email_imap_id', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_email_imap'),
            ['email_imap_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
