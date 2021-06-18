<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema\v1_4;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareInterface;
use Oro\Bundle\AttachmentBundle\Migration\Extension\AttachmentExtensionAwareTrait;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddFileRelation implements Migration, AttachmentExtensionAwareInterface
{
    use AttachmentExtensionAwareTrait;

    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        if ($schema->hasTable('teachers_application')) {
            $this->attachmentExtension->addFileRelation(
                $schema,
                'teachers_application',
                'attachment'
            );
        }
    }
}
