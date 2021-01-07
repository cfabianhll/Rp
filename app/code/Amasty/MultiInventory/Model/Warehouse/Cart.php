<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Dispatch;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\Collection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator;
use Amasty\MultiInventory\Model\Warehouse\Item\ValidatorResultData;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Session\Quote\Proxy;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\Processor;

/**
 * Class Cart
 *
 * @codingStandardsIgnoreFile
 */
class Cart extends DataObject
{
    const PRODUCT_TYPE_GIFTCARD = 'giftcard';

    /**
     * @var Quote\ItemFactory
     */
    private $quoteItemWhFactory;

    /**
     * @var WarehouseQuoteItemRepositoryInterface
     */
    private $quoteItemWhRepository;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var System
     */
    private $system;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $checkoutCart;

    /**
     * @var Processor
     */
    private $itemProcessor;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $stockCollection;
    /**
     * @var ItemRepository
     */
    private $stockRepository;

    /**
     * @var CollectionFactory
     */
    private $quoteItemWhCollection;

    /**
     * @var Item\QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var State
     */
    private $appState;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $backendQuote;

    /**
     * Cart constructor.
     *
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $itemCollection
     * @param ItemRepository $stockRepository
     * @param Quote\ItemFactory $quoteItemWhFactory
     * @param CollectionFactory $quoteItemWhCollection
     * @param WarehouseQuoteItemRepositoryInterface $quoteItemWhRepository
     * @param System $system
     * @param \Magento\Checkout\Model\Cart $checkoutCart
     * @param Dispatch $dispatch
     * @param Processor $itemProcessor
     * @param ManagerInterface $eventManager
     * @param QuantityValidator $quantityValidator
     * @param State $appState
     * @param Proxy $backendQuote
     * @param array $data
     */
    public function __construct(
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory $itemCollection,
        ItemRepository $stockRepository,
        \Amasty\MultiInventory\Model\Warehouse\Quote\ItemFactory $quoteItemWhFactory,
        CollectionFactory $quoteItemWhCollection,
        WarehouseQuoteItemRepositoryInterface $quoteItemWhRepository,
        System $system,
        \Magento\Checkout\Model\Cart $checkoutCart,
        Dispatch $dispatch,
        Processor $itemProcessor,
        ManagerInterface $eventManager,
        QuantityValidator $quantityValidator,
        State $appState,
        Proxy $backendQuote,
        array $data = []
    ) {
        parent::__construct($data);
        $this->quoteItemWhFactory    = $quoteItemWhFactory;
        $this->quoteItemWhRepository = $quoteItemWhRepository;
        $this->checkoutCart          = $checkoutCart;
        $this->dispatch              = $dispatch;
        $this->system                = $system;
        $this->itemProcessor         = $itemProcessor;
        $this->eventManager          = $eventManager;
        $this->stockCollection       = $itemCollection;
        $this->stockRepository       = $stockRepository;
        $this->quoteItemWhCollection = $quoteItemWhCollection;
        $this->quantityValidator = $quantityValidator;
        $this->appState = $appState;
        $this->backendQuote = $backendQuote;
    }

    /**
     * @return Dispatch
     */
    public function getDispatch()
    {
        if (!$this->hasData('dispatch')) {
            $this->setData('dispatch', $this->dispatch);
        }

        return $this->_getData('dispatch');
    }

    /**
     * @return Quote
     * @throws LocalizedException
     */
    public function getQuote()
    {
        if (!$this->hasData('quote')) {
            if ($this->appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
                $quote = $this->backendQuote->getQuote();
            } else {
                $quote = $this->getCheckoutCart()->getQuote();
            }
            $this->setData('quote', $quote);
        }

        return $this->_getData('quote');
    }

    /**
     * @param Quote $quote
     *
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->setData('quote', $quote);

        return $this;
    }

    /**
     * @param int $warehouseId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getGroupItems($warehouseId)
    {
        return $this->getItems()->addFieldToFilter('warehouse_id', $warehouseId)->getData();
    }

    /**
     * get involved warehouse ids
     *
     * @return array
     */
    public function getWarehouses()
    {
        $warehouses = [];

        $itemsCollection = $this->getItems();
        $itemsCollection->getSelect()->group('warehouse_id');
        foreach ($itemsCollection->getData() as $itemData) {
            $warehouses[] = $itemData['warehouse_id'];
        }

        return $warehouses;
    }

    /**
     * @return Collection
     * @throws LocalizedException
     */
    private function getItems()
    {
        return $this->quoteItemWhCollection->create()
            ->addFieldToFilter('quote_id', $this->getQuote()->getId());
    }

    /**
     * @return \Magento\Checkout\Model\Cart
     */
    public function getCheckoutCart()
    {
        return $this->checkoutCart;
    }

    /**
     * @return $this
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function clearItems()
    {
        $collection = $this->getItems();
        foreach ($collection as $item) {
            $this->quoteItemWhRepository->delete($item);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function addWhToItems()
    {
        $this->clearItems();
        if ($this->getQuote()->getItemsCount()) {
            $quoteItems = $this->getQuote()->getItemsCollection()->getItems();
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            foreach ($quoteItems as $quoteItem) {
                if ($quoteItem->getId() && !$quoteItem->isDeleted()) {
                    $this->addWhToQuote($quoteItem);
                }
            }
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     *
     * @throws CouldNotSaveException
     */
    public function addWhToQuote($quoteItem)
    {
        if ($result = $this->dispatchWarehouse($quoteItem)) {
            foreach ($result as $item) {
                $quoteWhItem = $this->quoteItemWhFactory->create();
                $quoteWhItem->addData($item->getData());
                $this->saveQuoteItemWarehouse($quoteWhItem);
            }
        }
    }

    /**
     * @param Quote\Item $quoteWhItemToSave
     *
     * @throws CouldNotSaveException
     */
    private function saveQuoteItemWarehouse($quoteWhItemToSave)
    {
        /** @var Quote\Item $quoteWhItem */
        $quoteWhItem = $this->quoteItemWhCollection->create()
            ->addFieldToFilter('quote_item_id', $quoteWhItemToSave->getQuoteItemId())
            ->addFieldToFilter('warehouse_id', $quoteWhItemToSave->getWarehouseId())
            ->setPageSize(1)
            ->getFirstItem();

        if (!$quoteWhItem->isObjectNew()) {
            $quoteWhItemToSave->setId($quoteWhItem->getId());
        }

        $this->quoteItemWhRepository->save($quoteWhItemToSave);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return ValidatorResultData[]
     */
    public function dispatchWarehouse($quoteItem)
    {
        $result = [];
        $this->getDispatch()->setCallables($this->system->getDispatchOrder());
        $this->getDispatch()->resetExclude();
        if ($quoteItem->getParentItemId()
            || $this->quantityValidator->isProductSimple($quoteItem->getProduct())
            || $quoteItem->getProduct()->getTypeId() == self::PRODUCT_TYPE_GIFTCARD
        ) {
            $this->prepareDispatch($quoteItem);
            $qty = $quoteItem->getQty();
            if ($quoteItem->getParentItem()) {
                $qty = $quoteItem->getParentItem()->getQty();
            }
            $checkResult = $this->quantityValidator->checkQuoteItemQty($quoteItem, $qty);
            $result = array_merge($result, $checkResult);
        }

        return $result;
    }

    /**
     * Set Dispatch Config
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    private function prepareDispatch($item)
    {
        $dispatch = $this->getDispatch();
        $dispatch->setQuoteItem($item);
        $dispatch->setDirection(Dispatch::DIRECTION_QUOTE);
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
     * @param Product $product
     * @param DataObject|null $request
     * @return mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addProduct(
        Product $product,
        DataObject $request = null
    ) {
        if (!$request instanceof DataObject) {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        if (!$product instanceof Product) {
            throw new LocalizedException(
                __('We found an invalid product for adding product to quote.')
            );
        }
        $processMode = AbstractType::PROCESS_MODE_FULL;
        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $parentItem = null;
        $errors = [];
        $item = null;
        $items = [];

        foreach ($cartCandidates as $candidate) {
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->itemProcessor->init($candidate, $request);
            $item->setQuote($this->getQuote());
            $item->setOptions($candidate->getCustomOptions());
            $item->setProduct($candidate);
            $this->getQuote()->addItem($item);
            $items[] = $item;
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }
            $this->itemProcessor->prepare($item, $request, $candidate);
            if ($item->getHasError()) {
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        $errors[] = $message;
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new LocalizedException(__(implode("\n", $errors)));
        }

        $this->eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);

        return $parentItem;
    }
}
