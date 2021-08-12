<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class RefundRefundedColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('teachers_refund');
        $table->addColumn('refunded', Types::BOOLEAN, ['notnull' => false]);
    }
}
