<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema\v1_8;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddDueTodayColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_invoice')) {
            $table = $schema->getTable('teachers_invoice');
            $table->addColumn('due_today', Types::BOOLEAN, ['notnull' => true]);
        }
    }
}
