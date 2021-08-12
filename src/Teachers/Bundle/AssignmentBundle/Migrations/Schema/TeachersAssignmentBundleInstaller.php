<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtension;
use Oro\Bundle\CommentBundle\Migration\Extension\CommentExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;
use Teachers\Bundle\AssignmentBundle\Entity\AssignmentMessage;

class TeachersAssignmentBundleInstaller implements Installation,
    ActivityExtensionAwareInterface,
    ExtendExtensionAwareInterface,
    CommentExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;
    /** @var ExtendExtension */
    protected $extendExtension;

    /** @var CommentExtension */
    protected $commentExtension;

    /**
     * @param CommentExtension $commentExtension
     */
    public function setCommentExtension(CommentExtension $commentExtension)
    {
        $this->commentExtension = $commentExtension;
    }

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

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
     * @throws SchemaException
     * @throws DBALException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createAssignmentPrivateNoteTable($schema);
        $this->createAssignmentMessageTable($schema);
        $this->createAssignmentTable($schema);
        $this->createAssignmentToTeacherGroupTable($schema);
        $this->addCommentToMessages($schema, $this->commentExtension);
    }

    /**
     * @param Schema $schema
     */
    protected function createAssignmentTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_assignment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('teacher_id', 'integer', ['notnull' => false]);
        $table->addColumn('student_id', 'integer', ['notnull' => false]);
        $table->addColumn('subject', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('course_manager_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);

        $this->activityExtension->addActivityAssociation($schema, 'teachers_assignment', 'oro_user');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_assignment', 'teachers_application');

        $this->activityExtension->addActivityAssociation($schema, 'oro_note', 'teachers_assignment');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_assignment_priv_note', 'teachers_assignment');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_assignment_message', 'teachers_assignment');

        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_assignment',
            'status',
            'assignment_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', Assignment::getAvailableStatuses());

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param Schema $schema
     */
    protected function createAssignmentToTeacherGroupTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_asmg_to_tg');
        $table->addColumn('assignment_id', 'integer', []);
        $table->addColumn('teacher_group_id', 'integer', []);
        $table->setPrimaryKey(['assignment_id', 'teacher_group_id']);
        $table->addIndex(['assignment_id'], 'teachers_as_to_tg_assignment_id_idx', []);
        $table->addIndex(['teacher_group_id'], 'teachers_as_to_tg_group_id_idx', []);
    }

    /**
     * Create orocrm_case_comment table
     *
     * @param Schema $schema
     */
    protected function createAssignmentPrivateNoteTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_assignment_priv_note');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('updated_by_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('message', 'text', []);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['updated_by_id'], 'trs_asg_prt_upd_by_idx', []);
        $table->addIndex(['owner_id'], 'trs_asg_prt_owner_idx', []);
        $table->addIndex(['organization_id'], 'trs_asg_prt_org_idx', []);
    }

    /**
     * Create orocrm_case_comment table
     *
     * @param Schema $schema
     */
    protected function createAssignmentMessageTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_assignment_message');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('updated_by_id', 'integer', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->addColumn('message', 'text', []);
        $table->addColumn('createdAt', 'datetime', []);
        $table->addColumn('updatedAt', 'datetime', []);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['updated_by_id'], 'trs_asg_msg_upd_by_idx', []);
        $table->addIndex(['owner_id'], 'trs_asg_msg_owner_idx', []);
        $table->addIndex(['organization_id'], 'trs_asg_msg_org_idx', []);

        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_assignment_message',
            'status',
            'assignment_msg_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', AssignmentMessage::getAvailableStatuses());

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    /**
     * @throws SchemaException
     * @throws DBALException
     */
    public static function addCommentToMessages(Schema $schema, CommentExtension $commentExtension)
    {
        $commentExtension->addCommentAssociation($schema, 'teachers_assignment_message');
    }
}
