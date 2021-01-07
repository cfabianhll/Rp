<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface;
use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Logger\Logger;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\Warehouse\OrderItemRepository;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Model\Order\Item;

/**
 * Catalog inventory module observer
 */
class CancelOrderItemObserver implements ObserverInterface
{
    /**
     * @var StockManagementInterface
     */
    private $helper;

    /**
     * @var OrderItemRepository
     */
    private $orderItemRepository;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var System
     */
    protected $system;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * CancelOrderItemObserver constructor.
     *
     * @param Data $helper
     * @param WarehouseOrderItemRepositoryInterface $orderItemRepository
     * @param WarehouseItemRepositoryInterface $stockRepository
     * @param System $system
     * @param Logger $logger
     * @param Processor $processor
     */
    public function __construct(
        Data $helper,
        WarehouseOrderItemRepositoryInterface $orderItemRepository,
        WarehouseItemRepositoryInterface $stockRepository,
        System $system,
        Logger $logger,
        Processor $processor
    ) {
        $this->helper = $helper;
        $this->orderItemRepository = $orderItemRepository;
        $this->stockRepository = $stockRepository;
        $this->system = $system;
        $this->logger = $logger;
        $this->processor = $processor;
    }

    /**
     * Cancel order item
     *
     * @param EventObserver $observer
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->system->isMultiEnabled()) {
            return;
        }
        /** @var Item $item */
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

        if ($item->getId() && $item->getProductId() && empty($children) && $qty > 0) {
            $warehouseId = $this->orderItemRepository->getByOrderItemId($item->getId())->getWarehouseId();
            $productId = $item->getProductId();

            /** @var WarehouseItemInterface $stockItem */
            $stockItem = $this->stockRepository->getByProductWarehouse($productId, $warehouseId);

            if (!$stockItem->getId()) {
                return;
            }
            $oldQty = $stockItem->getQty();
            $this->recalculateQuantity($stockItem, $oldQty, $qty);

            $this->stockRepository->save($stockItem);
            $this->logInfo($stockItem, $oldQty);

            $this->processor->reindexRow($productId);
        }
    }

    /**
     * @param WarehouseItemInterface $stockItem
     * @param float $oldQty
     */
    protected function logInfo($stockItem, $oldQty)
    {
        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                'cancel'
            );
        }
    }

    /**
     * @param WarehouseItemInterface $stockItem
     * @param float $oldQty
     * @param float $qty
     */
    private function recalculateQuantity($stockItem, $oldQty, $qty)
    {
        if ($stockItem->getShipQty() > 0) {
            $stockItem->setShipQty($stockItem->getShipQty() - $qty);
        } else {
            $stockItem->setQty($oldQty + $qty);
        }
        $stockItem->recalcAvailable();

        if ($stockItem->getAvailableQty() > 0) {
            $stockItem->setStockStatus(Stock::STOCK_IN_STOCK);
        }
    }
}
