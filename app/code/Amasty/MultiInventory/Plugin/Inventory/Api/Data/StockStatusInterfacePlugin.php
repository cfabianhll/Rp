<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Api\Data;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Magento\Framework\Module\Manager;

/**
 * Class StockStatusInterfacePlugin
 */
class StockStatusInterfacePlugin
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * StockStatusInterfacePlugin constructor.
     *
     * @param System $system
     * @param Data $dataHelper
     *
     */
    public function __construct(
        System $system,
        Data $dataHelper,
        Manager $moduleManager
    ) {
        $this->system = $system;
        $this->dataHelper = $dataHelper;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Modify Stock Status
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject
     * @param callable $proceed
     *
     * @return bool
     */
    public function aroundGetStockStatus(
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject,
        callable $proceed
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $proceed();
        }
        $productId = $subject->getProductId();

        $stock = $this->dataHelper->getStock($productId);

        if (!$stock->getId()) {
            return $proceed();
        }

        return $stock->getStockStatus();
    }

    /**
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject
     * @param callable                                                $proceed
     *
     * @return int
     */
    public function aroundGetQty(
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject,
        callable $proceed
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $proceed();
        }
        $productId = $subject->getProductId();

        $stock = $this->dataHelper->getStock($productId);

        if (!$stock->getId()) {
            return $proceed();
        }

        return $stock->getRealQty();
    }

    /**
     * Set right product backorders if module Amasty_Preorder is enabled
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function afterGetStockItem(
        \Magento\CatalogInventory\Api\Data\StockStatusInterface $subject,
        \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
    ) {
        if (!$this->system->isMultiEnabled() || !$this->moduleManager->isEnabled('Amasty_Preorder')) {
            return $stockItem;
        }
        $productId = $stockItem->getProductId();
        $stockWh = $this->dataHelper->getStock($productId);

        if ($stockWh->getId()) {
            $backorders = (int)$stockWh->getBackordersValue();
            $stockItem->setBackorders($backorders);
            $backorders != \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION ?:
                $stockItem->setUseConfigBackorders(false);
        }

        return $stockItem;
    }
}
