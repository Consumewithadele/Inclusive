<?php

namespace Inclusive\WebinarsEvents\Setup;

use Inclusive\WebinarsEvents\Model\Product\Type\EventOrWebinar;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $tableName = $installer->getTable('inclusive_events_schedule');

        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()->newTable(
                $tableName
            )->addColumn(
                'date_location_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Product Id'
            )->addColumn(
                'location',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Event type'
            )->addColumn(
                'date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Event Date'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Event type'
            )->addColumn(
                'address',
                Table::TYPE_TEXT,
                '64k',
                ['nullable' => true],
                'Event address'
            )->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Sort Order'
            )->addForeignKey(
                $installer->getFkName('inclusive_events_schedule', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_SET_NULL
            )->addIndex(
                $installer->getIdxName('inclusive_events_schedule', ['date_location_id']),
                ['date_location_id']
            );

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
