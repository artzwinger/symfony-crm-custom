<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_16;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MakeUserLoginPasswordColumnsNullable implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('teachers_assignment');
        $table->changeColumn('user_login', ['length' => 255, 'notnull' => false]);
        $table->changeColumn('user_password', ['length' => 255, 'notnull' => false]);
    }
}
