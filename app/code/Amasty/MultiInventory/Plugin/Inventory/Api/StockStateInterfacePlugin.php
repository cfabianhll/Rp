<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Api;

use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Warehouse\Cart;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Class StockStateInterfacePlugin
 */
class StockStateInterfacePlugin
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $warehouseStockRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var WarehouseQuoteItemRepositoryInterface
     */
    private $whQuoteItemRepository;

    /**
     * StockStateInterfacePlugin constructor.
     *
     * @param System $system
     * @param WarehouseItemRepositoryInterface $warehouseStockRepository
     * @param WarehouseQuoteItemRepositoryInterface $whQuoteItemRepository
     * @param Cart $cart
     */
    public function __construct(
        System $system,
        WarehouseItemRepositoryInterface $warehouseStockRepository,
        WarehouseQuoteItemRepositoryInterface $whQuoteItemRepository,
        Cart $cart
    ) {
        $this->system = $system;
        $this->warehouseStockRepository = $warehouseStockRepository;
        $this->cart = $cart;
        $this->whQuoteItemRepository = $whQuoteItemRepository;
    }

    /**
     * @param StockStateInterface $subject
     * @param callable $proceed
     * @param int $productId
     * @param float $qty
     * @param int $scopeId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCheckQty(
        StockStateInterface $subject,
        callable $proceed,
        $productId,
        $qty,
        $scopeId = null
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $proceed($productId, $qty, $scopeId);
        }

        $result = true;
        $totalQty = 0;

        /** @var Item $quoteItem */
        foreach ($this->cart->getQuote()->getAllItems() as $quoteItem) {
            if ($quoteItem->getProductId() == $productId) {

                $warehouseId = $this->whQuoteItemRepository->getWarehouseIdByItem($quoteItem);
                if (!$warehouseId) {
                    break;
                }
                $stock = $this->warehouseStockRepository
                    ->getByProductWarehouse($quoteItem->getProductId(), $warehouseId);

                $result &= $stock->getRealQty() - $quoteItem->getQty() >= 0 || $stock->isCanBackorder();
                $totalQty += $quoteItem->getQty();
            }
        }
        if ($totalQty == $qty) {
            return (bool) $result;
        }

        return $proceed($productId, $qty, $scopeId);
    }
}
