<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class PaymentTransactionColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('teachers_payment');
        $table->addColumn('transaction', Types::STRING, ['notnull' => false]);
    }
}
