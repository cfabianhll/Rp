<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Model\ResourceModel\Stock;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Dispatch;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Stock\Status;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\StoreResolverInterface;

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
     * @var Item
     */
    private $warehouseStockResource;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var bool
     */
    private $isFilterInStock;

    public function __construct(
        System $system,
        Item $warehouseStockResource,
        Dispatch $dispatch,
        StoreResolverInterface $storeResolver,
        DataObjectFactory $objectFactory
    ) {
        $this->system = $system;
        $this->warehouseStockResource = $warehouseStockResource;
        $this->dispatch = $dispatch;
        $this->storeResolver = $storeResolver;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status  $subject
     * @param Collection $collection
     * @param bool $isFilterInStock
     */
    public function beforeAddStockDataToCollection($subject, $collection, $isFilterInStock)
    {
        $this->isFilterInStock = $isFilterInStock;
    }

    /**
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $subject
     * @param Collection $collection
     *
     * @return Collection $collection
     * @throws LocalizedException
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Json_Exception
     */
    public function afterAddStockDataToCollection($subject, $collection)
    {
        if (!$this->system->isMultiEnabled()
            || !$this->system->isLockOnStore()
            || $collection->getFlag('warehouse_index_joined')
        ) {
            return $collection;
        }

        $this->dispatch->setCallables($this->system->getDispatchOrder());
        $this->dispatch->setDirection(Dispatch::DIRECTION_STORE);
        $this->dispatch->searchWh();
        $warehouseIds = $this->dispatch->getWarehousesRaw();

        if (empty($warehouseIds)) {
            return $collection;
        }
        $conditionTemplate = 'e.entity_id = %1$s.product_id AND %1$s.warehouse_id = ?';
        $stockColumn = $this->updateStockColumn($collection);

        $alias = 'warehouse_index';
        $joinCondition = $this->warehouseStockResource->getConnection()->quoteInto(
            sprintf($conditionTemplate, $alias),
            current($warehouseIds)
        );
        $collection->getSelect()->joinLeft(
            [$alias => $this->warehouseStockResource->getMainTable()],
            $joinCondition,
            []
        );
        $stockColumn = $collection->getConnection()
            ->getIfNullSql('warehouse_index.stock_status', $stockColumn);
        $collection->getSelect()->columns(
            ['is_salable' => $stockColumn]
        );
        $collection->setFlag('warehouse_index_joined', true);

        if ($this->isFilterInStock) {
            $this->updateInStockFilter($collection, $stockColumn);
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     *
     * @return int|string
     * @throws \Zend_Db_Select_Exception
     */
    private function updateStockColumn($collection)
    {
        $stockColumn = 0;
        $columns = $collection->getSelect()->getPart(Select::COLUMNS);

        foreach ($columns as $key => $column) {
            if (isset($column[2]) && $column[2] == 'is_salable') {
                $stockColumn = $column[0] . '.' . $column[1];
                unset($columns[$key]);
                break;
            }
        }
        $collection->getSelect()->setPart(Select::COLUMNS, $columns);

        return $stockColumn;
    }

    /**
     * @param Collection $collection
     * @param \Zend_Db_Expr $stockColumn
     *
     * @throws \Zend_Db_Select_Exception
     */
    private function updateInStockFilter($collection, $stockColumn)
    {
        $where = $collection->getSelect()->getPart(Select::WHERE);

        foreach ($where as $key => $column) {
            if (strpos($column, 'stock_status_index.stock_status') !== false) {
                unset($where[$key]);
                break;
            }
        }
        $collection->getSelect()->setPart(Select::WHERE, $where);
        $collection->getSelect()->where(
            $stockColumn . ' = ?',
            Status::STATUS_IN_STOCK
        );
    }
}
