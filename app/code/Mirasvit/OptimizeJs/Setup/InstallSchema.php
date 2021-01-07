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



namespace Mirasvit\OptimizeJs\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mirasvit\OptimizeJs\Api\Data\BundleFileInterface;

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
            $installer->getTable(BundleFileInterface::TABLE_NAME)
        )->addColumn(
            BundleFileInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            BundleFileInterface::ID
        )->addColumn(
            BundleFileInterface::AREA,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            BundleFileInterface::AREA
        )->addColumn(
            BundleFileInterface::LAYOUT,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            BundleFileInterface::LAYOUT
        )->addColumn(
            BundleFileInterface::THEME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            BundleFileInterface::THEME
        )->addColumn(
            BundleFileInterface::LOCALE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            BundleFileInterface::LOCALE
        )->addColumn(
            BundleFileInterface::FILENAME,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            BundleFileInterface::FILENAME
        )->addIndex(
            $installer->getIdxName(BundleFileInterface::TABLE_NAME, [
                BundleFileInterface::FILENAME,
            ]),
            [
                BundleFileInterface::FILENAME,
            ]
        );

        $connection->dropTable($setup->getTable(BundleFileInterface::TABLE_NAME));
        $connection->createTable($table);
    }
}
