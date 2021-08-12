<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\InvoiceBundle\Entity\Invoice;

class TeachersInvoiceBundleInstaller implements Installation, ActivityExtensionAwareInterface, ExtendExtensionAwareInterface
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
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createInvoiceTable($schema);
        $this->createPaymentTable($schema);
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     */
    protected function createInvoiceTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_invoice');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('assignment_id', 'integer', ['notnull' => false]);
        $table->addColumn('student_id', 'integer', ['notnull' => false]);
        $table->addColumn('student_contact_id', 'integer', ['notnull' => false]);
        $table->addColumn('student_account_id', 'integer', ['notnull' => false]);
        $table->addColumn('amount_owed', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
        $table->addColumn('amount_paid', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
        $table->addColumn('amount_remaining', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
        $table->addColumn('due_date', 'datetime', ['notnull' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['organization_id'], 'invoice_org_id_idx', []);
        $table->addIndex(['student_id'], 'invoice_student_id_idx', []);
        $this->activityExtension->addActivityAssociation($schema, 'teachers_invoice', 'teachers_assignment');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_invoice', 'oro_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('teachers_assignment'),
            ['assignment_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );

        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_invoice',
            'status',
            'invoice_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', Invoice::getAvailableStatuses());
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     */
    protected function createPaymentTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_payment');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('amount_paid', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
        $table->addColumn('invoice_id', 'integer', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->addColumn('organization_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['invoice_id'], 'invoice_invoice_id_idx', []);
        $table->addIndex(['organization_id'], 'payment_org_id_idx', []);
        $table->addIndex(['owner_id'], 'payment_owner_id_idx', []);
        $this->activityExtension->addActivityAssociation($schema, 'teachers_payment', 'teachers_invoice');
        $this->activityExtension->addActivityAssociation($schema, 'teachers_payment', 'oro_user');
        $table->addForeignKeyConstraint(
            $schema->getTable('teachers_invoice'),
            ['invoice_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
