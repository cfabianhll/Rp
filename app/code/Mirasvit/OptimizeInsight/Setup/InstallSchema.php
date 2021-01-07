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
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\OptimizeInsight\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\OptimizeInsight\Api\Data\ScoreInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $connection = $installer->getConnection();

        $installer->startSetup();

        $table = $connection->newTable(
            $installer->getTable(ScoreInterface::TABLE_NAME)
        )->addColumn(
            ScoreInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            ScoreInterface::ID
        )->addColumn(
            ScoreInterface::CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            ScoreInterface::CODE
        )->addColumn(
            ScoreInterface::VALUE,
            Table::TYPE_DECIMAL,
            '12,1',
            ['nullable' => false],
            ScoreInterface::VALUE
        )->addColumn(
            ScoreInterface::URL,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            ScoreInterface::URL
        )->addColumn(
            ScoreInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            ScoreInterface::CREATED_AT
        );

        $connection->dropTable($setup->getTable(ScoreInterface::TABLE_NAME));
        $connection->createTable($table);
    }
}
