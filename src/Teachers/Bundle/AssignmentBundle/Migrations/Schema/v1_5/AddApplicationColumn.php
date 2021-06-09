<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddApplicationColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment')) {
            $table = $schema->getTable('teachers_assignment');
            if (!$table->hasColumn('application_id')) {
                $table->addColumn('application_id', Types::INTEGER, ['notnull' => false]);
                $table->addIndex(['application_id'], 'asmg_app_id', []);
            }
        }
    }
}
