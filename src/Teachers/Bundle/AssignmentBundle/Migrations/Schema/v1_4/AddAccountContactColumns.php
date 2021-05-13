<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_4;

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
        if ($schema->hasTable('teachers_assignment')) {
            $table = $schema->getTable('teachers_assignment');
            $table->addColumn('student_contact_id', Types::INTEGER, ['notnull' => false]);
            $table->addColumn('student_account_id', Types::INTEGER, ['notnull' => false]);
            $table->addColumn('teacher_contact_id', Types::INTEGER, ['notnull' => false]);
            $table->addColumn('teacher_account_id', Types::INTEGER, ['notnull' => false]);
            $table->addIndex(['student_contact_id'], 'teachers_assignment_st_ct_id', []);
            $table->addIndex(['student_account_id'], 'teachers_assignment_st_ac_id', []);
            $table->addIndex(['teacher_contact_id'], 'teachers_assignment_tc_ct_id', []);
            $table->addIndex(['teacher_account_id'], 'teachers_assignment_tc_ac_id', []);
        }
    }
}
