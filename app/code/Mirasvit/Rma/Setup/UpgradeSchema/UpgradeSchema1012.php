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

class UpgradeSchema1012 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_attachment'),
            'created_at',
            'created_at',
            [
                'type'     => Table::TYPE_TIMESTAMP,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_message'),
            'created_at',
            'created_at',
            [
                'type'     => Table::TYPE_TIMESTAMP,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_item'),
            'created_at',
            'created_at',
            [
                'type'     => Table::TYPE_TIMESTAMP,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
        $installer->getConnection()->changeColumn(
            $installer->getTable('mst_rma_rma'),
            'created_at',
            'created_at',
            [
                'type'     => Table::TYPE_TIMESTAMP,
                'unsigned' => false,
                'nullable' => true,
            ]
        );
    }
}