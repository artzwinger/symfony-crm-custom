<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
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
            $table->addColumn('user_login', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('user_password', 'string', ['length' => 255, 'notnull' => true]);
        }
    }
}
