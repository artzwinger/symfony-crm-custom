<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddAccountContactColumns implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');
            $table->addColumn('student_contact_id', Types::INTEGER, ['notnull' => false]);
            $table->addColumn('student_account_id', Types::INTEGER, ['notnull' => false]);
            $table->addIndex(['student_contact_id'], 'teachers_application_st_ct_id', []);
            $table->addIndex(['student_account_id'], 'teachers_application_st_ac_id', []);
        }
    }
}
