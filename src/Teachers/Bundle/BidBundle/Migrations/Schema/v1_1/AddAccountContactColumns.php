<?php

namespace Teachers\Bundle\BidBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddAccountContactColumns implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_bid')) {
            $table = $schema->getTable('teachers_bid');
            $table->addColumn('teacher_contact_id', Types::INTEGER, ['notnull' => false]);
            $table->addColumn('teacher_account_id', Types::INTEGER, ['notnull' => false]);
            $table->addIndex(['teacher_contact_id'], 'teachers_bid_tc_ct_id', []);
            $table->addIndex(['teacher_account_id'], 'teachers_bid_tc_ac_id', []);
        }
    }
}
