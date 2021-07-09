<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_6;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddAmountDueToday implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');
            if (!$table->hasColumn('amount_due_today')) {
                $table->addColumn('amount_due_today', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => false]);
            }
        }
    }
}
