<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtension;
use Oro\Bundle\ActivityBundle\Migration\Extension\ActivityExtensionAwareInterface;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class CreateRefundEntity implements Migration, ActivityExtensionAwareInterface
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
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if (!$schema->hasTable('teachers_refund')) {
            $table = $schema->createTable('teachers_refund');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('amount_refunded', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => true]);
            $table->addColumn('invoice_id', 'integer', ['notnull' => false]);
            $table->addColumn('payment_id', 'integer', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime');
            $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
            $table->addColumn('owner_id', 'integer', ['notnull' => false]);
            $table->addColumn('organization_id', 'integer', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['invoice_id'], 'refund_invoice_id_idx', []);
            $table->addIndex(['payment_id'], 'refund_payment_id_idx', []);
            $table->addIndex(['organization_id'], 'refund_org_id_idx', []);
            $table->addIndex(['owner_id'], 'refund_owner_id_idx', []);
            if ($schema->hasTable('teachers_payment')) {
                $this->activityExtension->addActivityAssociation($schema, 'teachers_refund', 'teachers_payment');
            }
            $this->activityExtension->addActivityAssociation($schema, 'teachers_refund', 'oro_user');
            $table->addForeignKeyConstraint(
                $schema->getTable('teachers_invoice'),
                ['invoice_id'],
                ['id'],
                ['onDelete' => 'CASCADE', 'onUpdate' => null]
            );
            $table->addForeignKeyConstraint(
                $schema->getTable('teachers_payment'),
                ['payment_id'],
                ['id'],
                ['onDelete' => 'CASCADE', 'onUpdate' => null]
            );
        }
    }
}
