<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_10;

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
        $table = $schema->getTable('teachers_application');
        $table->changeColumn('user_login', ['length' => 255, 'notnull' => false]);
        $table->changeColumn('user_password', ['length' => 255, 'notnull' => false]);
    }
}
