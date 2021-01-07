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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Credit\Setup\UpgradeSchema;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema102 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mst_credit_product_option_type_credit')
        )
            ->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
                'Option Id')
            ->addColumn(
                'option_product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false],
                'Product ID')
            ->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => true],
                'Store Id')
            ->addColumn(
                'option_price_type',
                Table::TYPE_TEXT,
                20,
                ['unsigned' => false, 'nullable' => false, 'default' => 'fixed'],
                'Option Price Type')
            ->addColumn(
                'option_price_options',
                Table::TYPE_TEXT,
                20,
                ['unsigned' => false, 'nullable' => false],
                'Option Price Options')
            ->addColumn(
                'option_price',
                Table::TYPE_FLOAT,
                null,
                ['unsigned' => false, 'nullable' => false],
                'Option Price')
            ->addColumn(
                'option_credits',
                Table::TYPE_FLOAT,
                null,
                ['unsigned' => false, 'nullable' => true, 'default' => 0],
                'Option Credits')
            ->addColumn(
                'option_min_credits',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => true, 'default' => 0],
                'Option Min Credits')
            ->addColumn(
                'option_max_credits',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => true, 'default' => 0],
                'Option Max Credits')
        ;
        $installer->getConnection()->createTable($table);
    }
}