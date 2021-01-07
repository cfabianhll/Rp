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

class UpgradeSchema108 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_status'),
            'name',
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_reason'),
            'name',
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_condition'),
            'name',
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_resolution'),
            'name',
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_field'),
            'name',
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_rule'),
            'name',
            'name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_rule'),
            'email_subject',
            'email_subject',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 65535,
            ]
        );
    }
}