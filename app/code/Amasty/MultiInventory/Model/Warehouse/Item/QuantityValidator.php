<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Item;

use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Config\Source\BackordersAction;
use Amasty\MultiInventory\Model\Dispatch;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Helper\Data as StockHelper;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Math\Division as MathDivision;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Item;

/**
 * Class QuantityValidator
 */
class QuantityValidator
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var System
     */
    private $system;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $warehouseStockRepository;

    /**
     * @var ValidatorResultDataFactory
     */
    private $resultDataFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MathDivision
     */
    private $mathDivision;

    /**
     * QuantityValidator constructor.
     *
     * @param Data $helper
     * @param System $system
     * @param WarehouseItemRepositoryInterface $warehouseStockRepository
     * @param StockRegistryInterface $stockRegistry
     * @param ValidatorResultDataFactory $resultDataFactory
     * @param RequestInterface $request
     * @param MathDivision $mathDivision
     */
    public function __construct(
        Data $helper,
        System $system,
        WarehouseItemRepositoryInterface $warehouseStockRepository,
        StockRegistryInterface $stockRegistry,
        ValidatorResultDataFactory $resultDataFactory,
        RequestInterface $request,
        MathDivision $mathDivision
    ) {
        $this->helper = $helper;
        $this->system = $system;
        $this->stockRegistry = $stockRegistry;
        $this->warehouseStockRepository = $warehouseStockRepository;
        $this->resultDataFactory = $resultDataFactory;
        $this->request = $request;
        $this->mathDivision = $mathDivision;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Json_Exception
     */
    public function validate(Observer $observer)
    {
        /** @var $quoteItem Item */
        $quoteItem = $observer->getEvent()->getItem();

        if (!$this->system->isMultiEnabled()
            || !$quoteItem
            || !$quoteItem->getProductId()
            || !$quoteItem->getQuote()
            || $quoteItem->getQuote()->getIsSuperMode()
        ) {
            return;
        }
        $product = $quoteItem->getProduct();
        $stock = $this->stockRegistry->getStockItem(
            $product->getId(),
            $quoteItem->getStore()->getWebsiteId()
        );

        if (!$this->checkStockItem($stock, $quoteItem)) {
            return;
        }

        $qtyIncrements = $stock->getQtyIncrements() * 1;

        if (!$this->checkQuoteItem($quoteItem, $qtyIncrements)) {
            return;
        }
        $this->helper->getDispatch()->setCallables($this->system->getDispatchOrder())
            ->resetExclude()
            ->setDirection(Dispatch::DIRECTION_QUOTE)
            ->setQuoteItem($quoteItem);
        $requestedQty = $quoteItem->getQty();

        if ($quoteItem->getParentItem()) {
            $requestedQty = $quoteItem->getParentItem()->getQty();
        }
        $this->checkQuoteItemQty($quoteItem, $requestedQty);
    }

    /**
     * @param StockItemInterface $stock
     * @param Item $quoteItem
     *
     * @return bool
     */
    protected function checkStockItem($stock, $quoteItem)
    {
        if (!$stock->getManageStock()) {
            return false;
        }

        if ($stock->getMinSaleQty() && $quoteItem->getQty() < $stock->getMinSaleQty()) {
            return false;
        }

        if ($stock->getMaxSaleQty() && $quoteItem->getQty() > $stock->getMaxSaleQty()) {
            return false;
        }

        return true;
    }

    /**
     * @param Item $quoteItem
     * @param int $qtyIncrements
     *
     * @return bool
     */
    protected function checkQuoteItem($quoteItem, $qtyIncrements)
    {
        if ($qtyIncrements && $this->mathDivision->getExactDivision($quoteItem->getQty(), $qtyIncrements) != 0) {
            return false;
        }

        if (!empty($quoteItem->getErrorInfos())) {
            return false;
        }

        if (!$quoteItem->getParentItem() && !$this->isProductSimple($quoteItem->getProduct())) {
            // remove errors for configurable product. Later will be checked for their children
            $this->removeErrorsFromQuoteAndItem($quoteItem, StockHelper::ERROR_QTY);
            return false;
        }

        return true;
    }

    /**
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param int|null $requestedQty
     *
     * @return ValidatorResultData[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Json_Exception
     * @since 1.3.0 quantity validation and backorders status
     */
    public function checkQuoteItemQty($quoteItem, $requestedQty = null)
    {
        $product = $quoteItem->getProduct();

        if ($requestedQty === null) {
            $requestedQty = $quoteItem->getQty();
        }

        /**
         * Check current quote items and if this product exists in quote,
         * change requested qty to check quantity in stock on add to cart step
         */
        if ($this->request->isPost()) {
            $this->updateRequestedQty($quoteItem, $product, $requestedQty);
        }
        $results = [];
        $dispatcher = $this->helper->getDispatch();
        $iteration = 0;

        do {
            $dispatcher->searchWh();
            $warehouse = $dispatcher->getWarehouse();

            if ($warehouse == $dispatcher->getDefaultWarehouseId() && $iteration > 0) {
                $this->notEnoughQty($quoteItem, $results, $requestedQty);
                break;
            }
            $results[] = $result = $this->getValidatorResult($requestedQty, $product, $warehouse, $quoteItem);

            if ($iteration > 0) {
                $result->setIsSplitted(true);
            }
            $itemStock = $this->warehouseStockRepository->getByProductWarehouse($product->getId(), $warehouse);
            $this->proceedStockItem($itemStock, $quoteItem);
            $warehouseQty = $itemStock->getRealQty();
            $stockFounded = true;
            // if not enough qty on one warehouse, then split item and take qty for multiple warehouses
            if ($warehouseQty < $requestedQty) {
                if (!$this->checkMultiWhItemQty(
                    $itemStock,
                    $warehouseQty,
                    $requestedQty,
                    $quoteItem,
                    $product,
                    $result,
                    $dispatcher,
                    $warehouse,
                    $stockFounded
                )) {
                    break;
                }
            }
            ++$iteration;
        } while ($stockFounded === false);

        return $results;
    }

    /**
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param Product $product
     * @param int $requestedQty
     */
    private function updateRequestedQty($quoteItem, $product, &$requestedQty)
    {
        $quote = $quoteItem->getQuote();

        if ($quote) {
            $items = $quote->getAllItems();

            /** @var Item $item */
            foreach ($items as $item) {
                if (($item->getProduct()->getSku() == $product->getSku())
                    && ($item->getItemId() != $quoteItem->getItemId())
                ) {
                    $requestedQty = $item->getQty();
                }
            }
        }
    }

    /**
     * @param int $requestedQty
     * @param Product $product
     * @param int $warehouse
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     *
     * @return ValidatorResultData
     */
    private function getValidatorResult($requestedQty, $product, $warehouse, $quoteItem)
    {
        $result = $this->resultDataFactory->create()
            ->setQty($requestedQty)
            ->setProductId($product->getId())
            ->setWarehouseId($warehouse);

        if ($quoteItem instanceof Item) {
            $result->setQuoteId($quoteItem->getQuoteId())
                ->setQuoteItemId($quoteItem->getItemId());
        } else {
            $result->setOrderId($quoteItem->getOrderId())
                ->setOrderItemId($quoteItem->getItemId());
        }

        return $result;
    }

    /**
     * @param WarehouseItemInterface $itemStock
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     */
    private function proceedStockItem($itemStock, $quoteItem)
    {
        if ($itemStock->getStockStatus() == Stock::STOCK_OUT_OF_STOCK) {
            $quoteItem->addErrorInfo(
                'cataloginventory',
                StockHelper::ERROR_QTY,
                __('This product is out of stock.')
            );
            $quoteItem->getQuote()->addErrorInfo(
                'stock',
                'cataloginventory',
                StockHelper::ERROR_QTY,
                __('Some of the products are out of stock.')
            );
        } else {
            // Delete error from item and its quote, if it was set due to item out of stock
            $this->removeErrorsFromQuoteAndItem($quoteItem, StockHelper::ERROR_QTY);

            if ($quoteItem->getParentItem()) {
                $this->removeErrorsFromQuoteAndItem($quoteItem->getParentItem(), StockHelper::ERROR_QTY);
            }
        }
    }

    /**
     * @param WarehouseItemInterface $itemStock
     * @param int $warehouseQty
     * @param int $requestedQty
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param Product $product
     * @param ValidatorResultData $result
     * @param Dispatch $dispatcher
     * @param int $warehouse
     * @param bool $stockFounded
     *
     * @return bool
     */
    private function checkMultiWhItemQty(
        $itemStock,
        $warehouseQty,
        &$requestedQty,
        $quoteItem,
        $product,
        $result,
        $dispatcher,
        $warehouse,
        &$stockFounded
    ) {
        if ($this->system->getBackordersAction() == BackordersAction::DO_NOT_SPLIT
            && $itemStock->isCanBackorder()
        ) {
            if ($warehouseQty > 0) {
                $requestedQty -= $warehouseQty;
            }
            $this->processQuoteBackorder($quoteItem, $itemStock, $requestedQty, $result);

            return false;
        }

        if ($warehouseQty > 0) {
            $result->setIsChanged(true);
            $stockFounded = false;
            $requestedQty -= $warehouseQty;
            $result->setQty($warehouseQty);
            $dispatcher->addExclude($product->getId(), $warehouse);
        } elseif ($itemStock->isCanBackorder()) {
            $this->processQuoteBackorder($quoteItem, $itemStock, $requestedQty, $result);

            return false;
        } else {
            $this->processQuoteStockError($quoteItem, $product);

            return false;
        }

        return true;
    }

    /**
     * @param Product $item
     * @return bool
     */
    public function isProductSimple($item)
    {
        return $item->getTypeId() == Type::TYPE_SIMPLE;
    }

    /**
     * @param Item $quoteItem
     * @param ValidatorResultData[] $results
     * @param int $requestedQty
     */
    private function notEnoughQty($quoteItem, $results, $requestedQty)
    {
        foreach ($results as $result) {
            $itemStock = $this->warehouseStockRepository
                ->getByProductWarehouse($quoteItem->getProduct()->getId(), $result->getWarehouseId());

            if ($itemStock->isCanBackorder()) {
                $result->setQty($result->getQty() + $requestedQty);
                $this->processQuoteBackorder($quoteItem, $itemStock, $requestedQty, $result);

                return;
            }
        }
        $this->processQuoteStockError($quoteItem, $quoteItem->getProduct());
    }

    /**
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param \Amasty\MultiInventory\Model\Warehouse\Item                     $stock
     * @param int                                                             $backorderQty
     * @param ValidatorResultData                                             $result
     */
    private function processQuoteBackorder($quoteItem, $stock, $backorderQty, $result)
    {
        if ($quoteItem instanceof \Magento\Sales\Model\Order\Item) {
            $quoteItem->setQtyBackordered($backorderQty);

            return;
        }

        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }
        $result->setBackorderedQty($backorderQty);
        $quoteItem->setBackorders($backorderQty);

        if ($stock->isShowBackorderNotice()) {
            $backorderPhrase = __(
                'We don\'t have as many "%1" as you requested, but we\'ll back order the remaining %2.',
                $quoteItem->getProduct()->getName(),
                $backorderQty
            );
            $quoteItem->addMessage($backorderPhrase);
        }
    }

    /**
     * @param Item|\Magento\Sales\Model\Order\Item $quoteItem
     * @param Product $product
     */
    private function processQuoteStockError($quoteItem, $product)
    {
        $errorMessage = __('We don\'t have as many "%1" as you requested.', $product->getName());
        $quoteItem->addErrorInfo(
            'amasty_inventory',
            StockHelper::ERROR_QTY,
            $errorMessage
        );

        if ($quoteItem->getParentItem()) {
            $quoteItem->getParentItem()->addErrorInfo(
                'amasty_inventory',
                StockHelper::ERROR_QTY,
                $errorMessage
            );
        }
        $quoteItem->getProduct()->setHasError(true);
        $quoteItem->getQuote()->addErrorInfo(
            'amasty_inventory',
            StockHelper::ERROR_QTY,
            $errorMessage
        );
    }

    /**
     * Removes error statuses from quote and item, set by this observer
     *
     * @param Item $item
     * @param int $code
     * @return void
     */
    private function removeErrorsFromQuoteAndItem($item, $code)
    {
        if ($item->getHasError()) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $item->removeErrorInfosByParams($params);
        }
        $quote = $item->getQuote();

        if (empty($quote)) {
            return;
        }
        $quoteItems = $quote->getItemsCollection();
        $canRemoveErrorFromQuote = $this->checkQuoteItemsForErrors($quoteItems, $item, $code);

        if ($quote->getHasError() && $canRemoveErrorFromQuote) {
            $params = ['origin' => 'cataloginventory', 'code' => $code];
            $quote->removeErrorInfosByParams(null, $params);
        }
        $messages = $item->getMessage(false);

        if (is_array($messages)) {
            $this->processMessages($messages, $item);
        }
        $item->setUseOldQty(false);
    }

    /**
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $quoteItems
     * @param Item $item
     * @param int $code
     *
     * @return bool
     */
    private function checkQuoteItemsForErrors($quoteItems, $item, $code)
    {
        $canRemoveErrorFromQuote = true;

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getItemId() == $item->getItemId()) {
                continue;
            }
            $errorInfos = $quoteItem->getErrorInfos();

            foreach ($errorInfos as $errorInfo) {
                if ($errorInfo['code'] == $code) {
                    $canRemoveErrorFromQuote = false;
                    break;
                }
            }

            if (!$canRemoveErrorFromQuote) {
                break;
            }
        }

        return $canRemoveErrorFromQuote;
    }

    /**
     * @param array $messages
     * @param Item $item
     */
    private function processMessages($messages, $item)
    {
        /** @var Phrase $messagePhrase */
        foreach ($messages as $messagePhrase) {
            if ($messagePhrase instanceof Phrase) {
                $text = $messagePhrase->getText();
            } else {
                $text = $messagePhrase;
            }

            if (strpos($text, 'We don\'t have') !== false) {
                $item->removeMessageByText($messagePhrase);
                $item->removeErrorInfosByParams(['message' => $messagePhrase]);
            }
        }
    }
}
