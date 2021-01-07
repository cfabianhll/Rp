<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\CatalogInventory\Stock;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Dispatch;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class StatusPlugin
 */
class StatusPlugin
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var ProductMetadataInterface
     */
    private $metadata;

    public function __construct(
        System $system,
        Dispatch $dispatch,
        ProductMetadataInterface $metadata
    ) {
        $this->system = $system;
        $this->dispatch = $dispatch;
        $this->metadata = $metadata;
    }

    /**
     * @param Status $stockStatus
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param $isFilterInStock
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Json_Exception
     */
    public function aroundAddStockDataToCollection(
        Status $stockStatus,
        \Closure $proceed,
        $collection,
        $isFilterInStock
    ) {
        if (!$this->system->isMultiEnabled()
            || !$this->system->isLockOnStore()
            || version_compare($this->metadata->getVersion(), '2.3.0', '<')
            || !$this->checkIsSimple($collection)
        ) {
            return $proceed($collection, $isFilterInStock);
        }
        $this->dispatch->setCallables($this->system->getDispatchOrder());
        $this->dispatch->setDirection(Dispatch::DIRECTION_STORE);
        $this->dispatch->searchWh();
        $warehouse = $this->dispatch->getWarehouse();

        $resource = $collection->getResource();
        $subselect = clone $collection->getSelect();

        $subselect->reset()->from(
            ['total_stock' => $resource->getTable('amasty_multiinventory_warehouse_item')],
            [
                'product_id' => 'total_stock.product_id',
                'total' => 'total_stock.stock_status',
            ]
        )->joinLeft(
            ['current_stock' => $resource->getTable('amasty_multiinventory_warehouse_item')],
            $collection->getConnection()->quoteInto(
                'total_stock.product_id = current_stock.product_id AND current_stock.warehouse_id = ?',
                $warehouse
            ),
            ['current' => 'current_stock.stock_status']
        )->where('total_stock.warehouse_id = ?', $this->dispatch->getDefaultWarehouseId());

        $collection->getSelect()
            ->joinLeft(
                ['wh_item' => $subselect],
                'e.entity_id = wh_item.product_id',
                ['is_salable' => 'if (wh_item.current IS NOT NULL, wh_item.current, wh_item.total)']
            );

        return $collection;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return bool
     */
    private function checkIsSimple($collection)
    {
        $collectionData = $collection->getConnection()->fetchAll($collection->getSelect());
        $oneItem = array_shift($collectionData);

        if (isset($oneItem['type_id']) && $oneItem['type_id'] !== 'simple') {
            return false;
        }

        return true;
    }
}
