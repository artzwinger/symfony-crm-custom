<?php

namespace Teachers\Bundle\BidBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddBidViewedColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_bid')) {
            $table = $schema->getTable('teachers_bid');
            $table->addColumn('un_viewed', Types::BOOLEAN, ['notnull' => true, 'default' => '1']);
        }
    }
}
