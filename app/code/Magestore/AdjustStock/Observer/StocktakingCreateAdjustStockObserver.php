<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\AdjustStock\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\AdjustStock\Api\AdjustStock\AdjustStockManagementInterface;
use Magestore\AdjustStock\Api\Data\AdjustStock\AdjustStockInterface;
use Magestore\AdjustStock\Api\Data\AdjustStock\AdjustStockInterfaceFactory;
use Magestore\AdjustStock\Api\Data\AdjustStock\ProductInterface as AdjustStockProductInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection as StocktakingItemCollection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Adjust Stock - Stocktaking Create Adjust Stock Observer
 */
class StocktakingCreateAdjustStockObserver implements ObserverInterface
{

    /**
     * @var AdjustStockManagementInterface
     */
    protected $adjustStockManagement;

    /**
     * @var AdjustStockInterfaceFactory
     */
    protected $adjustStockFactory;

    /**
     * StocktakingCreateAdjustStockObserver constructor
     *
     * @param AdjustStockManagementInterface $adjustStockManagement
     * @param AdjustStockInterfaceFactory $adjustStockFactory
     */
    public function __construct(
        AdjustStockManagementInterface $adjustStockManagement,
        AdjustStockInterfaceFactory $adjustStockFactory
    ) {
        $this->adjustStockManagement = $adjustStockManagement;
        $this->adjustStockFactory = $adjustStockFactory;
    }

    /**
     * Process create adjust stock from stocktaking
     *
     * @param EventObserver $observer
     * @return $this
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $eventData = $observer->getEvent()->getEventData();

        /** @var StocktakingInterface $stocktaking */
        $stocktaking = $eventData->getStocktaking();
        /** @var StocktakingItemCollection $stocktakingItems */
        $stocktakingItems = $eventData->getStocktakingItems();

        $isCreatedAdjustStock = $eventData->getIsCreatedAdjustStock();
        if ($isCreatedAdjustStock) {
            return $this;
        }

        // Prepare adjust stock's data
        $adjustData = [];
        $adjustData[AdjustStockInterface::SOURCE_CODE] = $stocktaking->getSourceCode();
        $adjustData[AdjustStockInterface::SOURCE_NAME] = $stocktaking->getSourceName();
        $adjustData[AdjustStockInterface::REASON] = __("Adjust from stock-taking " . $stocktaking->getCode());

        foreach ($stocktakingItems as $item) {
            $changeQty = $item->getCountedQty() - $item->getQtyInSource();
            $adjustData['products'][$item->getProductId()] = [
                AdjustStockProductInterface::PRODUCT_ID => $item->getProductId(),
                AdjustStockProductInterface::PRODUCT_SKU => $item->getProductSku(),
                AdjustStockProductInterface::PRODUCT_NAME => $item->getProductName(),
                AdjustStockProductInterface::OLD_QTY => $item->getQtyInSource(),
                AdjustStockProductInterface::CHANGE_QTY => $changeQty,
                AdjustStockProductInterface::NEW_QTY => $item->getCountedQty(),
            ];
        }

        // Create adjust stock
        $adjustStock = $this->adjustStockFactory->create();
        try {
            $this->adjustStockManagement->createAdjustment(
                $adjustStock,
                $adjustData
            );
    
            // Apply adjust stock
            $this->adjustStockManagement->complete($adjustStock);
        } catch (\Exception $e) {
            if ($adjustStock->getId()) {
                $adjustStock->getResource()->delete($adjustStock);
            }
            throw new LocalizedException(__($e->getMessage()), $e);
        }

        $eventData->setIsCreatedAdjustStock(true);
        return $this;
    }
}
