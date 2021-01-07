<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @codingStandardsIgnoreFile
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_multiinventory_warehouse')
        )->addColumn(
            'warehouse_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Warehouse ID'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Warehouse Title'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            [],
            'Warehouse Code'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0', 'primary' => true],
            'Warehouse Store'
        )->addColumn(
            'country',
            Table::TYPE_TEXT,
            255,
            [],
            'Country'
        )->addColumn(
            'state',
            Table::TYPE_TEXT,
            255,
            [],
            'State'
        )->addColumn(
            'city',
            Table::TYPE_TEXT,
            255,
            [],
            'City'
        )->addColumn(
            'address',
            Table::TYPE_TEXT,
            255,
            [],
            'Address'
        )->addColumn(
            'zip',
            Table::TYPE_TEXT,
            255,
            [],
            'Zip'
        )->addColumn(
            'phone',
            Table::TYPE_TEXT,
            255,
            [],
            'Phone'
        )->addColumn(
            'email',
            Table::TYPE_TEXT,
            255,
            [],
            'Email'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            null,
            [],
            'Description'
        )->addColumn(
            'manage',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Manage stock'
        )->addColumn(
            'priority',
            Table::TYPE_INTEGER,
            5,
            ['nullable' => false, 'default' => '0'],
            'Priority'
        )->addColumn(
            'is_general',
            Table::TYPE_SMALLINT,
            null,
            [],
            'General Stock'
        )->addColumn(
            'order_email_notification',
            Table::TYPE_TEXT,
            255,
            [],
            'Order Email Notification'
        )->addColumn(
            'low_stock_notification',
            Table::TYPE_TEXT,
            255,
            [],
            'Low Stock Notification'
        )->addColumn(
            'stock_id',
            Table::TYPE_INTEGER,
            2,
            [],
            'Inventory Stock'
        )->addColumn(
            'create_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addColumn(
            'update_time',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
            'Modification Time'
        )->setComment(
            'Warehouses Table'
        );
        $installer->getConnection()->createTable($table);

        $describe = $installer->getConnection()->describeTable($installer->getTable('customer_group'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_multiinventory_customer_group')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Warehouse Customer ID'
        )->addColumn(
            'warehouse_id',
            Table::TYPE_INTEGER,
            2,
            [],
            'Warehouse Id'
        )->addColumn(
            'group_id',
            $describe['customer_group_id']['DATA_TYPE'] == 'int' ? Table::TYPE_INTEGER : Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addIndex(
            $installer->getIdxName('amasty_multiinventory_customer_group', ['warehouse_id']),
            ['warehouse_id']
        )->addForeignKey(
            $installer->getFkName(
                'amasty_multiinventory_customer_group',
                'warehouse_id',
                'amasty_multiinventory_warehouse',
                'warehouse_id'
            ),
            'warehouse_id',
            $installer->getTable('amasty_multiinventory_warehouse'),
            'warehouse_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'amasty_multiinventory_customers',
                'group_id',
                'customer_group',
                'customer_group_id'
            ),
            'group_id',
            $installer->getTable('customer_group'),
            'customer_group_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Warehouse Customers Table'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_multiinventory_store')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Warehouse Customer ID'
        )->addColumn(
            'warehouse_id',
            Table::TYPE_INTEGER,
            2,
            [],
            'Warehouse Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addIndex(
            $installer->getIdxName('amasty_multiinventory_customer_group', ['warehouse_id']),
            ['warehouse_id']
        )->addForeignKey(
            $installer->getFkName(
                'amasty_multiinventory_store',
                'warehouse_id',
                'amasty_multiinventory_warehouse',
                'warehouse_id'
            ),
            'warehouse_id',
            $installer->getTable('amasty_multiinventory_warehouse'),
            'warehouse_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'amasty_multiinventory_store',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Warehouse Store'
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_multiinventory_warehouse_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Item ID'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product entity ID'
            )
            ->addColumn(
                'warehouse_id',
                Table::TYPE_INTEGER,
                2,
                [],
                'Warehouse Id'
            )
            ->addColumn(
                'qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'Qty'
            )
            ->addColumn(
                'available_qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Available Qty'
            )
            ->addColumn(
                'ship_qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Qty To Ship'
            )
            ->addColumn(
                'room_shelf',
                Table::TYPE_TEXT,
                '255',
                [],
                'Room & Shelf'
            )
            ->addIndex(
                $installer->getIdxName(
                    'amasty_multiinventory_warehouse_item',
                    ['product_id', 'warehouse_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id', 'warehouse_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('amasty_multiinventory_warehouse_item', ['warehouse_id']),
                ['warehouse_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_multiinventory_warehouse_item',
                    'warehouse_id',
                    'amasty_multiinventory_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('amasty_multiinventory_warehouse'),
                'warehouse_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Warehouse Item');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_multiinventory_warehouse_order_item'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Item ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order ID'
            )
            ->addColumn(
                'order_item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order Item ID'
            )
            ->addColumn(
                'warehouse_id',
                Table::TYPE_INTEGER,
                2,
                [],
                'Warehouse Id'
            )
            ->addIndex(
                $installer->getIdxName('amasty_multiinventory_warehouse_order_item', ['warehouse_id']),
                ['warehouse_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_multiinventory_warehouse_order_item',
                    'warehouse_id',
                    'amasty_multiinventory_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('amasty_multiinventory_warehouse'),
                'warehouse_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_multiinventory_warehouse_order_item',
                    'order_id',
                    'sales_order',
                    'entity_id'
                ),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_multiinventory_warehouse_order_item',
                    'order_item_id',
                    'sales_order_item',
                    'item_id'
                ),
                'order_item_id',
                $installer->getTable('sales_order_item'),
                'item_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Warehouse Item');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_multiinventory_warehouse_import'))
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Item ID'
            )
            ->addColumn(
                'warehouse_id',
                Table::TYPE_INTEGER,
                2,
                [],
                'Warehouse Id'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product entity ID'
            )
            ->addColumn(
                'qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'Qty'
            )
            ->addColumn(
                'new_qty',
                Table::TYPE_DECIMAL,
                '12,4',
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'New Qty'
            )
            ->addColumn(
                'import_number',
                Table::TYPE_TEXT,
                255,
                [],
                'Number Import'
            )
            ->addIndex(
                $installer->getIdxName('amasty_multiinventory_warehouse_import', ['warehouse_id']),
                ['warehouse_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_multiinventory_warehouse_import',
                    'warehouse_id',
                    'amasty_multiinventory_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('amasty_multiinventory_warehouse'),
                'warehouse_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Warehouse Import');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_multiinventory_export'))
            ->addColumn(
                'export_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Export Id'
            )
            ->addColumn(
                'file',
                Table::TYPE_TEXT,
                255,
                [],
                'Export File'
            )
            ->addColumn(
                'create_time',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->setComment('Export Files');

        $installer->getConnection()->createTable($table);
    }
}
