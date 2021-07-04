<?php

namespace Teachers\Bundle\InvoiceBundle\Migrations\Schema\v1_3;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class GeneratedIds implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_invoice')) {
            $queries->addQuery('ALTER TABLE teachers_invoice AUTO_INCREMENT = 1000');
        }
        if ($schema->hasTable('teachers_payment')) {
            $queries->addQuery('ALTER TABLE teachers_payment AUTO_INCREMENT = 1000');
        }
        if ($schema->hasTable('teachers_refund')) {
            $queries->addQuery('ALTER TABLE teachers_refund AUTO_INCREMENT = 1000');
        }
    }
}
