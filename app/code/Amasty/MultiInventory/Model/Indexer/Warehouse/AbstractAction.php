<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Indexer\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;
use Psr\Log\LoggerInterface;

/**
 * Abstract action reindex class
 */
abstract class AbstractAction
{
    const MULTI_INVENTORY_TABLE = 'amasty_multiinventory_warehouse_item';

    const INVENTORY_TABLE = 'cataloginventory_stock_item';

    private $updateStockStatus = false;

    /**
     * Resource instance
     *
     * @var Resource
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var System
     */
    private $helper;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AbstractAction constructor.
     *
     * @param ResourceConnection                        $resource
     * @param WarehouseFactory                          $warehouseFactory
     * @param System      $helper
     * @param CacheContext   $cacheContext
     * @param ManagerInterface $eventManager
     * @param LoggerInterface                           $logger
     */
    public function __construct(
        ResourceConnection $resource,
        WarehouseFactory $warehouseFactory,
        System $helper,
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->eventManager = $eventManager;
        $this->warehouseFactory = $warehouseFactory;
        $this->cacheContext = $cacheContext;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     *
     * @return void
     */
    abstract public function execute($ids);

    /**
     * @return AdapterInterface
     */
    public function getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }

    /**
     * @param $entityName
     * @return mixed
     */
    public function getTable($entityName)
    {
        return $this->resource->getTableName($entityName);
    }

    /**
     * Reindex all
     *
     * @return AbstractAction
     */
    public function reindexAll()
    {
        $this->getConnection()->beginTransaction();
        try {
            $select = $this->scopeQuery();
            $this->insert($select);
            $this->updateInventory($select);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
        }
        if ($this->helper->isMultiEnabled()) {
            $this->createEvent();
        }

        return $this;
    }

    /**
     * Sometimes after insert in setup:upgrade values of total stock gets duplicated,
     * checking for duplicated rows and remove them after reindex and setup:upgrade
     *
     * @param string $table
     * @param bool $isWh
     */
    private function removeDuplicates($table, $isWh = true)
    {
        $defaultId = $this->warehouseFactory->create()->getDefaultId();
        $subSelect = $this->getConnection()->select()->from(
            [$table],
            ['item_id', 'product_id']
        )->group(
            'product_id'
        )->having(
            new \Zend_Db_Expr('COUNT(1) > 1')
        )->order('item_id DESC');

        if ($isWh) {
            $subSelect->where(
                new \Zend_Db_Expr(sprintf('warehouse_id = ABS(%s)', $defaultId))
            );
        }

        $select = $this->getConnection()->select()->from(
            ['e' => $table],
            ['e.item_id']
        )->joinRight(
            ['sub' => $subSelect],
            'e.product_id = sub.product_id',
            []
        )->where(
            'sub.item_id <> e.item_id'
        );

        if ($isWh) {
            $select->where(
                new \Zend_Db_Expr(sprintf('warehouse_id = ABS(%s)', $defaultId))
            );
        }
        $itemIds = array_keys($this->getConnection()->fetchAssoc($select->assemble()));

        if (!empty($itemIds)) {
            $this->getConnection()->delete(
                $table,
                ['item_id IN (?)' => $itemIds]
            );
        }
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function reindexRows($productIds = [])
    {
        $this->getConnection()->beginTransaction();
        try {
            if (!is_array($productIds)) {
                $productIds = [$productIds];
            }
            $select = $this->scopeQuery();
            if (count($productIds) > 0) {
                $select->where('main_table.product_id IN(?)', $productIds);
            }
            $this->insert($select);
            $this->updateInventory($select);
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->logger->error($e);

            $this->getConnection()->rollBack();
        }

        if ($this->helper->isMultiEnabled()) {
            $this->createEvent($productIds);
        }

        return $this;
    }

    /**
     * @return Select
     */
    private function scopeQuery()
    {
        $defaultId = $this->warehouseFactory->create()->getDefaultId();
        $warehousesNotActive = $this->warehouseFactory->create()->getWhNotActive();
        $warehousesNotActive[] = $defaultId;
        $arrayFields = ['qty', 'available_qty', 'ship_qty'];
        $columnArray['product_id'] = 'product_id';
        foreach ($arrayFields as $field) {
            $columnArray[$field] = new \Zend_Db_Expr(sprintf('SUM(%s)', $field));
        }
        $columnArray['warehouse_id'] = new \Zend_Db_Expr(sprintf('ABS(%s)', $defaultId));
        $columnArray[WarehouseItemInterface::STOCK_STATUS] = new \Zend_Db_Expr(
            sprintf('MAX(%s)', WarehouseItemInterface::STOCK_STATUS)
        );

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getTable(self::MULTI_INVENTORY_TABLE)],
            $columnArray
        );
        $select->group('main_table.product_id');
        $select->order('main_table.product_id');
        $select->where('main_table.warehouse_id NOT IN(?)', $warehousesNotActive);

        return $select;
    }

    /**
     * @param Select $query
     */
    private function insert($query)
    {
        $query = $this->getConnection()->insertFromSelect(
            $query,
            $this->getTable(self::MULTI_INVENTORY_TABLE),
            ['product_id', 'qty', 'available_qty', 'ship_qty', 'warehouse_id', WarehouseItemInterface::STOCK_STATUS],
            AdapterInterface::INSERT_ON_DUPLICATE
        );
        $this->getConnection()->query($query);
        $this->removeDuplicates($this->getTable(self::MULTI_INVENTORY_TABLE));
    }

    /**
     * @param Select $query
     */
    private function updateInventory(Select $query)
    {
        if ($this->helper->isMultiEnabled()) {
            $field = 'qty';
            if ($this->helper->getAvailableDecreese()) {
                $field = 'available_qty';
            }
            $query->reset(Select::COLUMNS);
            $query->columns([
                'stock_id' => new \Zend_Db_Expr(Stock::DEFAULT_STOCK_ID),
                'product_id',
                'qty' => new \Zend_Db_Expr(sprintf('SUM(%s)', $field)),
                'is_in_stock' => new \Zend_Db_Expr(sprintf('MAX(%s)', WarehouseItemInterface::STOCK_STATUS))
            ]);

            $query = $this->getConnection()->insertFromSelect(
                $query,
                $this->getTable(self::INVENTORY_TABLE),
                ['stock_id', 'product_id', 'qty', 'is_in_stock'],
                AdapterInterface::INSERT_ON_DUPLICATE
            );

            $this->getConnection()->query($query);
            $this->removeDuplicates($this->getTable(self::INVENTORY_TABLE), false);
        }
    }

    /**
     * @param array $productIds
     */
    public function createEvent($productIds = [])
    {
        $this->cacheContext->registerEntities(Warehouse::CACHE_TAG, $productIds);
        $this->eventManager->dispatch('amasty_multi_inventory_indexer', ['object' => $this->cacheContext]);
    }

    /**
     * @param boolean $status
     */
    public function setUpdateStockStatus($status)
    {
        $this->updateStockStatus = $status;
    }
}
