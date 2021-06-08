<?php

namespace Teachers\Bundle\BidBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropBidSubjectColumn implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_bid')) {
            $table = $schema->getTable('teachers_bid');
            if ($table->hasColumn('subject')) {
                $table->dropColumn('subject');
            }
        }
    }
}
