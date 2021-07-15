<?php

namespace Teachers\Bundle\AssignmentBundle\Migrations\Schema\v1_7;

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
        if ($schema->hasTable('teachers_assignment')) {
            $queries->addQuery('ALTER TABLE teachers_assignment AUTO_INCREMENT = 10000');
        }
    }
}
