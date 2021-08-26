<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_9;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddInvoiceDueTodayPaid implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_assignment')) {
            $table = $schema->getTable('teachers_assignment');
            if (!$table->hasColumn('invoice_due_today_paid')) {
                $table->addColumn('invoice_due_today_paid', Types::BOOLEAN, ['notnull' => false]);
            }
        }
    }
}
