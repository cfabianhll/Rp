<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Service\Warehouse;

use Magestore\FulfilSuccess\Service\PickRequest\PickRequestService;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * Fulfil - Warehouse Service
 */
class WarehouseService implements WarehouseServiceInterface
{

    /**
     * @var PickRequestService
     */
    protected $pickRequestService;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magestore\FulfilSuccess\Api\FulfilManagementInterface
     */
    protected $fulfilManagement;

    /**
     * @var \Magestore\FulfilSuccess\Api\MultiSourceInventory\SourceItemRepositoryInterface
     */
    protected $sourceItemRepository;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * WarehouseService constructor.
     *
     * @param PickRequestService $pickRequestService
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param ObjectManagerInterface $objectManager
     * @param \Magestore\FulfilSuccess\Api\FulfilManagementInterface $fulfilManagement
     * @param \Magestore\FulfilSuccess\Api\MultiSourceInventory\SourceItemRepositoryInterface $sourceItemRepository
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        PickRequestService $pickRequestService,
        \Magento\Framework\Module\Manager $moduleManager,
        ObjectManagerInterface $objectManager,
        \Magestore\FulfilSuccess\Api\FulfilManagementInterface $fulfilManagement,
        \Magestore\FulfilSuccess\Api\MultiSourceInventory\SourceItemRepositoryInterface $sourceItemRepository,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        StockResolverInterface $stockResolver
    ) {
        $this->pickRequestService = $pickRequestService;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->fulfilManagement = $fulfilManagement;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->stockResolver = $stockResolver;
    }

    /**
     * Get list warehouse to pick items
     * $productIds = [$itemId => $productId]
     * return [$itemId => [$warehouseId => $qty]]
     *
     * @param array $productIds
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $showOutStock
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getWarehousesToPick($productIds, $order, $showOutStock = false)
    {
        $isMSIEnable = $this->fulfilManagement->isMSIEnable();
        if (!$isMSIEnable) {
            return [];
        }
        
        $resourceProducts = $this->sourceItemRepository->getSourceItem($productIds, $order);

        $pickingQtys = $this->pickRequestService->getPickingQtyProducts($productIds);
        $warehouses = [];
        foreach ($resourceProducts as $resourceProduct) {
            $resource = $resourceProduct->getWarehouseId();
            $productId = $resourceProduct->getProductId();
            $pickingQty = isset($pickingQtys[$resource][$productId])
                ? $pickingQtys[$resource][$productId]
                : 0;

            // Always allow adding product if product is not manage stock
            $availableQty = $this->isManageStockSku($resourceProduct->getSku(), $order) ?
                max(0, $resourceProduct->getTotalQty() - $pickingQty)
                : self::MAX_AVAILABLE_QTY;

            if (!$showOutStock && !$availableQty) {
                /* do not show out-stock warehouse */
                continue;
            }
            $warehouses[$productId][$resource]['available_qty'] = $availableQty;
            $warehouses[$productId][$resource]['warehouse'] = $resourceProduct->getWarehouse();
            $warehouses[$productId][$resource]['high_priority'] = $resourceProduct->getData('high_priority');
        }

        /* transfer data to item-warehouse */
        $itemWarehouses = [];
        foreach ($productIds as $itemId => $productId) {
            if (!isset($warehouses[$productId])) {
                continue;
            }
            /* sort warehouse by available_qty */
            $itemWarehouses[$itemId] = $this->sortWarehouses($warehouses[$productId]);
        }

        return $itemWarehouses;
    }

    /**
     * Check product is managed stock
     *
     * @param int $productSku
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return boolean
     */
    public function isManageStockSku($productSku, $order)
    {
        $websiteCode = $order->getStore()->getWebsite()->getCode();
        $stockId = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        $stockItemConfiguration = $this->getStockItemConfiguration->execute(
            $productSku,
            $stockId
        );
        return $stockItemConfiguration->isManageStock();
    }

    /**
     * Sort Warehouses
     *
     * @param array $warehouses
     * @return array
     */
    public function sortWarehouses($warehouses)
    {
        $warehouses = $this->sortWarehousesByQty($warehouses);
        $isMSIEnable = $this->fulfilManagement->isMSIEnable();
        if ($isMSIEnable) {
            $warehouses = $this->sortWarehouseByStock($warehouses);
        }
        return $warehouses;
    }

    /**
     * Sort Warehouse By Stock
     *
     * @param array $warehouses
     * @return array
     */
    public function sortWarehouseByStock($warehouses)
    {
        $highPriorityWarehouses = [];
        foreach ($warehouses as $key => $warehouse) {
            if ($warehouse['high_priority']) {
                $highPriorityWarehouses[$key] = $warehouse;
                unset($warehouses[$key]);
            }
        }

        $result = [];
        // merge one by one to fix bug that wrong source code when source is number
        foreach ($highPriorityWarehouses as $k => $wh) {
            $result[$k] = $wh;
        }
        foreach ($warehouses as $k => $wh) {
            $result[$k] = $wh;
        }

        return $result;
    }

    /**
     * Sort warehouse list
     * $warehouses = [$warehouseId => ['available_qty' => $qty, 'warehouse' => $warehouse]]
     *
     * @param array $warehouses
     * @return array
     */
    public function sortWarehousesByQty($warehouses)
    {
        $sortedWarehouses = [];
        foreach ($warehouses as $warehouseId => &$warehouseData) {
            $warehouseData['warehouse_id'] = $warehouseId;
        }

        usort($warehouses, [$this, "sortWarehousesByQtyDESC"]);

        foreach ($warehouses as $warehouse) {
            $sortedWarehouses[$warehouse['warehouse_id']] = $warehouse;
        }

        return $sortedWarehouses;
    }

    /**
     * Compare lack_qty of warehouses
     *
     * @param array $warehouseA
     * @param array $warehouseB
     * @return int
     */
    public function sortWarehousesByQtyDESC($warehouseA, $warehouseB)
    {
        if ($warehouseA['available_qty'] == $warehouseB['available_qty']) {
            return 0;
        }
        if ($warehouseA['available_qty'] > $warehouseB['available_qty']) {
            return -1;
        }
        return 1;
    }
}
