<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema\v1_7;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddFullyPaidDateColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_invoice')) {
            $table = $schema->getTable('teachers_invoice');
            $table->addColumn('fully_paid_date', 'datetime', ['notnull' => true]);
        }
    }
}
