<?php

namespace Inclusive\WebinarsEvents\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('inclusive_events_schedule'),
                'end_date',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'length' => 10,
                    'nullable' => true,
                    'comment' => 'Event Ending Date'
                ]
            );
        }
        $installer->endSetup();
    }
}