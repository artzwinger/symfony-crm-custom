<?php

namespace Teachers\Bundle\BidBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\BidBundle\Entity\Bid;

class TeachersBidBundleInstaller implements Installation, ActivityExtensionAwareInterface, ExtendExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;
    /**
     * @var ExtendExtension
     */
    protected $extendExtension;

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
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
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
     * @throws \Doctrine\DBAL\Schema\SchemaException
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
        $table->addColumn('subject', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('assignment_id', 'integer', ['notnull' => false]);
        $table->addColumn('teacher_id', 'integer', ['notnull' => false]);
        $table->addColumn('price', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id'], 'teachers_bid_org_id_idx', []);
        $table->addIndex(['teacher_id'], 'teachers_bid_teacher_id_idx', []);
        $this->activityExtension->addActivityAssociation($schema, 'teachers_bid', 'teachers_assignment');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_bid', 'oro_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('teachers_assignment'),
            ['assignment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_bid',
            'status',
            'bid_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', Bid::getAvailableStatuses());
    }
}
