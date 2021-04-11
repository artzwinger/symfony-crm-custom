<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\AssignmentBundle\Entity\Assignment;

class TeachersAssignmentBundleInstaller implements Installation, ActivityExtensionAwareInterface, ExtendExtensionAwareInterface
{
    /** @var ActivityExtension */
    protected $activityExtension;
    /** @var ExtendExtension */
    protected $extendExtension;

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
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createAssignmentTable($schema);
        $this->createAssignmentToTeacherGroupTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
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
}
