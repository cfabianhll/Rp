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

class UpgradeSchema1011 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_resolution'),
            'exchange_order_enabled',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length'   => null,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Is Exchange order enabled'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_resolution'),
            'replacement_order_enabled',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length'   => null,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Is Replacement order enabled'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_resolution'),
            'creditmemo_enabled',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'length'   => null,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Creditmemo enabled'
            ]
        );

        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_rma_rma_replacement_order')
        )->addColumn(
            'rma_rorder_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Rma Order Id'
        )->addColumn(
            'rma_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Rma Id'
        )->addColumn(
            'replacement_order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Replacement Order Id'
        )->addColumn(
            'replacement_order_increment',
            Table::TYPE_TEXT,
            32,
            ['nullable' => true],
            'Replacement Order Increment'
        )->addIndex(
            $installer->getIdxName('mst_rma_rma_replacement_order', ['rma_id']),
            ['rma_id']
        )->addForeignKey(
            $installer->getFkName(
                'mst_rma_rma_replacement_order',
                'rma_id',
                'mst_rma_rma',
                'rma_id'
            ),
            'rma_id',
            $installer->getTable('mst_rma_rma'),
            'rma_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
    }
}