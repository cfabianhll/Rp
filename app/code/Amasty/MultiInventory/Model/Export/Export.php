<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Export;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Export
 */
class Export extends AbstractExport
{
    const MW_EXPORT_ENTITY = 'amasty_warehouse_export';

    const COLUMNS = [
        'source_code',
        'sku',
        'status',
        'quantity'
    ];

    /**
     * Export constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param CollectionFactory $collection
     * @param Item $item
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        CollectionFactory $collection,
        Item $item,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->collectionFactory = $collection;
        $this->_productsOnTotalStock = $item->getItemsOnTotalStock();
    }

    /**
     * Prepare collection. Add necessary fields
     *
     * @return void
     */
    public function addAttributesToCollection()
    {
        $mainTable = $this->collection->getMainTable();
        $this->collection->getSelect()->reset();
        $this->collection->getSelect()
            ->from(
                ['wh' => $mainTable],
                ['source_code' => 'title']
            )->joinRight(
                ['wh_item' => $this->collection->getTable('amasty_multiinventory_warehouse_item')],
                'wh.warehouse_id = wh_item.warehouse_id',
                ['status' => 'wh_item.stock_status', 'quantity' => 'wh_item.qty']
            )->joinLeft(
                ['product' => $this->collection->getTable('catalog_product_entity')],
                'wh_item.product_id = product.entity_id',
                ['sku' => 'product.sku']
            )->order('source_code');
    }

    /**
     * @return string
     */
    public function getEntityTypeCode()
    {
        return self::MW_EXPORT_ENTITY;
    }
}
