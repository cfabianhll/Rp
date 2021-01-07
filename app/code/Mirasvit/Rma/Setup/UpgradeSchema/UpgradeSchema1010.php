<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema1010 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mst_rma_rma'),
            $installer->getFkName(
                'mst_rma_rma',
                'order_id',
                'sales_order',
                'entity_id'
            )
        );
        $installer->getConnection()->dropForeignKey(
            $installer->getTable('mst_rma_rma'),
            $installer->getFkName(
                'mst_rma_rma',
                'customer_id',
                'customer_entity',
                'entity_id'
            )
        );

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rma_offline_order')
        )->addColumn(
            'offline_order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Offline Order Id'
        )->addColumn(
            'receipt_number',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Receipt Number'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Customer Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            5,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_offline_order',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rma_offline_item')
        )->addColumn(
            'offline_item_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Offline Order Item Id'
        )->addColumn(
            'rma_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'RMA Id'
        )->addColumn(
            'offline_order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Offline Order Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Name'
        )->addColumn(
            'reason_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Reason Id'
        )->addColumn(
            'resolution_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Resolution Id'
        )->addColumn(
            'condition_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Condition Id'
        )->addColumn(
            'qty_requested',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Qty Requested'
        )->addColumn(
            'qty_returned',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Qty Returned'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('mst_rma_offline_item', ['rma_id']),
            ['rma_id']
        )->addIndex(
            $installer->getIdxName('mst_rma_offline_item', ['reason_id']),
            ['reason_id']
        )->addIndex(
            $installer->getIdxName('mst_rma_offline_item', ['resolution_id']),
            ['resolution_id']
        )->addIndex(
            $installer->getIdxName('mst_rma_offline_item', ['condition_id']),
            ['condition_id']
        )->addIndex(
            $installer->getIdxName('mst_rma_offline_item', ['offline_order_id']),
            ['offline_order_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_offline_item',
                'condition_id',
                'mst_rma_condition',
                'condition_id'
            ),
            'condition_id',
            $installer->getTable('mst_rma_condition'),
            'condition_id',
            Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_offline_item',
                'reason_id',
                'mst_rma_reason',
                'reason_id'
            ),
            'reason_id',
            $installer->getTable('mst_rma_reason'),
            'reason_id',
            Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_offline_item',
                'resolution_id',
                'mst_rma_resolution',
                'resolution_id'
            ),
            'resolution_id',
            $installer->getTable('mst_rma_resolution'),
            'resolution_id',
            Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_offline_item',
                'rma_id',
                'mst_rma_rma',
                'rma_id'
            ),
            'rma_id',
            $installer->getTable('mst_rma_rma'),
            'rma_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_offline_item',
                'offline_order_id',
                'mst_rma_offline_order',
                'offline_order_id'
            ),
            'offline_order_id',
            $installer->getTable('mst_rma_offline_order'),
            'offline_order_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
    }
}