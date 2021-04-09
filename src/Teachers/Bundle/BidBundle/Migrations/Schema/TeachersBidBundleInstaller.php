<?php

namespace Teachers\Bundle\BidBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class TeachersBidBundleInstaller implements Installation, ActivityExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;

    /**
     * {@inheritdoc}
     */
    public function setActivityExtension(ActivityExtension $activityExtension)
    {
        $this->activityExtension = $activityExtension;
    }

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
        $this->createBidTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function createBidTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_bid');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('assignment_id', 'integer', ['notnull' => false]);
        $table->addColumn('teacher_id', 'integer', ['notnull' => false]);
        $table->addColumn('price', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id'], 'teachers_bid_org_id_idx', []);
        $table->addIndex(['owner_id'], 'teachers_bid_owner_id_idx', []);
        $this->activityExtension->addActivityAssociation($schema, 'teachers_bid', 'teachers_teacher');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_bid', 'teachers_course_manager');
        $table->addForeignKeyConstraint(
            $schema->getTable('teachers_assignment'),
            ['assignment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('teachers_teacher'),
            ['teacher_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
