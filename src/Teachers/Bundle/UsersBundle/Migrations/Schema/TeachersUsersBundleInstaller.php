<?php

namespace Teachers\Bundle\UsersBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class TeachersUsersBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createGroupTable($schema);
        $this->createTeacherGroupToUserTable($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function createGroupTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_group');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('title', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    protected function createTeacherGroupToUserTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_tg_to_usr');
        $table->addColumn('teacher_group_id', 'integer', []);
        $table->addColumn('user_id', 'integer', []);
        $table->setPrimaryKey(['teacher_group_id', 'user_id']);
        $table->addIndex(['teacher_group_id'], 'teachers_tgtu_group_id_idx', []);
        $table->addIndex(['user_id'], 'teachers_tgtu_user_id_idx', []);
    }
}
