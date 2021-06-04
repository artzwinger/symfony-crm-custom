<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class UpdateApplicationColumns implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');

            if (!$table->hasColumn('studentLoginInfo') && $table->hasColumn('first_name')) {
                return;
            }

            $table->dropColumn('subject');
            $table->dropColumn('studentLoginInfo');

            $table->addColumn('first_name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('last_name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('email', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('phone', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('course_name', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('course_prefixes', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('work_today', Types::BOOLEAN, ['notnull' => true]);
            $table->addColumn('due_date', 'datetime', ['notnull' => true]);
            $table->addColumn('course_url', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('user_login', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('user_password', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('instructions', 'text', ['notnull' => false]);
        }
    }
}
