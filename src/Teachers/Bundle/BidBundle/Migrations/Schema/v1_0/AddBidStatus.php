<?php

namespace Teachers\Bundle\BidBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\BidBundle\Entity\Bid;

class AddBidStatus implements Migration, ExtendExtensionAwareInterface
{
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Generate table orocrm_call **/
        $table = $schema->createTable('teachers_bid');

        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_bid',
            'status',
            'bid_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', Bid::getAvailableStatuses());

        $enumTable->addOption(OroOptions::KEY, $options);
    }
}
