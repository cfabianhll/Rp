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



namespace Mirasvit\OptimizeImage\Setup\UpgradeSchema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;

class UpgradeSchema101 implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();

        if ($connection->isTableExists($setup->getTable(FileInterface::TABLE_NAME))) {
            $connection->dropTable($setup->getTable(FileInterface::TABLE_NAME));
        }

        $table = $connection->newTable(
            $setup->getTable(FileInterface::TABLE_NAME)
        )->addColumn(
            FileInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            FileInterface::ID
        )->addColumn(
            FileInterface::BASENAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FileInterface::BASENAME
        )->addColumn(
            FileInterface::RELATIVE_PATH,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            FileInterface::RELATIVE_PATH
        )->addColumn(
            FileInterface::FILE_EXTENSION,
            Table::TYPE_TEXT,
            5,
            ['nullable' => false],
            FileInterface::FILE_EXTENSION
        )->addColumn(
            FileInterface::ORIGINAL_SIZE,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => false],
            FileInterface::ORIGINAL_SIZE
        )->addColumn(
            FileInterface::ACTUAL_SIZE,
            Table::TYPE_INTEGER,
            11,
            ['nullable' => true],
            FileInterface::ACTUAL_SIZE
        )->addColumn(
            FileInterface::CREATED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            FileInterface::CREATED_AT
        )->addColumn(
            FileInterface::PROCESSED_AT,
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            FileInterface::PROCESSED_AT
        )->addIndex(
            $setup->getIdxName(FileInterface::TABLE_NAME, [FileInterface::PROCESSED_AT]),
            [FileInterface::PROCESSED_AT]
        )->addIndex(
            $setup->getIdxName(FileInterface::TABLE_NAME, [FileInterface::RELATIVE_PATH]),
            [FileInterface::RELATIVE_PATH]
        );

        $connection->createTable($table);
    }
}
