<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Setup\Operation;

use Amasty\Storelocator\Model\ResourceModel\LocationProductIndex;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeTo230 for create locations index table
 */
class UpgradeTo230
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function addLocationsIndexTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(LocationProductIndex::TABLE_NAME))
            ->addColumn(
                LocationProductIndex::LOCATION_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Location Id'
            )
            ->addColumn(
                LocationProductIndex::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Product ID'
            )
            ->addColumn(
                LocationProductIndex::STORE_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Store ID'
            )
            ->addIndex(
                $setup->getIdxName(
                    LocationProductIndex::TABLE_NAME,
                    [
                        LocationProductIndex::LOCATION_ID,
                        LocationProductIndex::PRODUCT_ID,
                        LocationProductIndex::STORE_ID
                    ]
                ),
                [
                    LocationProductIndex::LOCATION_ID,
                    LocationProductIndex::PRODUCT_ID,
                    LocationProductIndex::STORE_ID
                ]
            )
            ->setComment('Amasty Index Locations Table');

        $setup->getConnection()->createTable($table);
    }
}
