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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema1015 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_status'),
            'children_ids',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => false,
                'comment'  => 'Next Allowed Status'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_status'),
            'is_visible',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length'   => 0,
                'default'  => 1,
                'nullable' => false,
                'comment'  => 'Visible in the frontend'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_status'),
            'is_main_branch',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length'   => 0,
                'default'  => 1,
                'nullable' => false,
                'comment'  => 'Main Branch'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_status'),
            'color',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 30,
                'nullable' => false,
                'comment'  => 'Color'
            ]
        );
        $installer->getConnection()->addColumn(
            $installer->getTable('mst_rma_rma'),
            'status_history',
            [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => false,
                'comment'  => 'Status History'
            ]
        );
    }
}