<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_5;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class DropExcessColumns implements Migration
{
    /**
     * @inheritDoc
     * @throws SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $table = $schema->getTable('teachers_application');
            if ($table->hasColumn('studentLoginInfo')) {
                $table->dropColumn('studentLoginInfo');
            }
            if ($table->hasColumn('subject')) {
                $table->dropColumn('subject');
            }
        }
    }
}
