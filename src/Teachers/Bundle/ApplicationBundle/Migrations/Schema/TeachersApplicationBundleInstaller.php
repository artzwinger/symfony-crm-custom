<?php

namespace Teachers\Bundle\ApplicationBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\EntityExtendBundle\EntityConfig\ExtendScope;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtension;
use Oro\Bundle\EntityExtendBundle\Migration\Extension\ExtendExtensionAwareInterface;
use Oro\Bundle\EntityExtendBundle\Migration\OroOptions;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Teachers\Bundle\ApplicationBundle\Entity\Application;

class TeachersApplicationBundleInstaller implements Installation, ExtendExtensionAwareInterface
{
    /** @var ExtendExtension */
    protected $extendExtension;

    /**
     * @inheritdoc
     */
    public function setExtendExtension(ExtendExtension $extendExtension)
    {
        $this->extendExtension = $extendExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion(): string
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $this->createApplicationTable($schema);
        $this->addStatusesEnum($schema);
        $this->addTermEnum($schema);
    }

    /**
     * @param Schema $schema
     */
    protected function addStatusesEnum(Schema $schema)
    {
        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_application',
            'status',
            'application_status'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', array_keys(Application::getAvailableStatuses()));
        $options->set('extend', 'owner', ExtendScope::OWNER_SYSTEM);

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param Schema $schema
     */
    protected function addTermEnum(Schema $schema)
    {
        $enumTable = $this->extendExtension->addEnumField(
            $schema,
            'teachers_application',
            'term',
            'application_term'
        );

        $options = new OroOptions();
        $options->set('enum', 'immutable_codes', array_keys(Application::getAvailableTerms()));
        $options->set('extend', 'owner', ExtendScope::OWNER_SYSTEM);

        $enumTable->addOption(OroOptions::KEY, $options);
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    protected function createApplicationTable(Schema $schema)
    {
        $table = $schema->createTable('teachers_application');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('subject', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('studentLoginInfo', 'text', ['notnull' => true]);
        $table->addColumn('price', 'money', ['precision' => 0, 'comment' => '(DC2Type:money)', 'notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('owner_id', 'integer', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['owner_id'], 'teachers_application_owner_id', []);
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_organization'),
            ['owner_id'],
            ['id'],
            ['onDelete' => 'SET NULL', 'onUpdate' => null]
        );
    }
}
