<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Quote\Model;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Cart;
use Amasty\MultiInventory\Model\Warehouse\QuoteItemRepository;
use Closure;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class QuoteManagement
 */
class QuoteManagement
{
    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var System
     */
    private $system;

    /**
     * @var QuoteItemRepository
     */
    private $itemQuoteRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item
     */
    private $itemResource;

    /**
     * QuoteManagement constructor.
     *
     * @param Cart                                       $cart
     * @param System                                              $system
     * @param CollectionFactory $itemCollectionFactory
     * @param \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item                   $itemResource
     * @param QuoteItemRepository                                                               $itemQuoteRepository
     * @param Registry                                                       $registry
     * @param DataObjectFactory                                              $objectFactory
     */
    public function __construct(
        Cart $cart,
        System $system,
        CollectionFactory $itemCollectionFactory,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item $itemResource,
        QuoteItemRepository $itemQuoteRepository,
        Registry $registry,
        DataObjectFactory $objectFactory
    ) {
        $this->cart                  = $cart;
        $this->itemQuoteRepository   = $itemQuoteRepository;
        $this->system                = $system;
        $this->registry              = $registry;
        $this->objectFactory         = $objectFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->itemResource          = $itemResource;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $object
     * @param Closure $work
     * @param Quote $quote
     * @param array $orderData
     *
     * @return AbstractExtensibleModel|OrderInterface|object|null
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function aroundSubmit(
        \Magento\Quote\Model\QuoteManagement $object,
        Closure $work,
        Quote $quote,
        $orderData = []
    ) {
        if ($this->system->isMultiEnabled() && $this->system->getDefinationWarehouse()) {
            $this->cart->getCheckoutCart()->setQuote($quote);
            $items = $this->itemResource->getCountOfItems($this->cart->getQuote()->getId());
            if ($items) {
                $this->separateItems($items);
                $this->save();
            }
        }

        return $work($quote, $orderData);
    }

    /**
     * Add to cart slitted items before order place
     *
     * @param array $items
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    private function separateItems($items)
    {
        $processedItems = [];

        foreach (array_keys($items) as $itemId) {
            $quoteItem = $this->cart->getQuote()->getItemById($itemId);

            /** @var \Amasty\MultiInventory\Model\Warehouse\Quote\Item[] $amItems */
            $amItems = $this->itemCollectionFactory->create()
                ->addFieldToFilter('quote_item_id', $itemId)
                ->getItems();
            $count = 1;

            foreach ($amItems as $item) {
                if ($count == 1) {
                    $this->checkParentItem($item, $quoteItem, $processedItems);
                    $processedItems[] = $quoteItem->getId();
                } else {
                    $info = $quoteItem->getBuyRequest();
                    $product = $quoteItem->getProduct();

                    if ($quoteItem->getParentItem()) {
                        $product = $quoteItem->getParentItem()->getProduct();
                        $info = $quoteItem->getParentItem()->getBuyRequest();
                    }
                    $info->setQty($item->getQty());
                    $info->setOriginalQty($item->getQty());
                    $info->unsetData('uenc');

                    /** @var Item $newQuoteItem */
                    $newQuoteItem = $this->cart->addProduct($product, $info);
                    $newQuoteItem->setProduct($product);
                    $newQuoteItem->save();

                    $processedItems[] = $newQuoteItem->getId();
                    $optionAdded = $this->checkChildrensForQuoteItem(
                        $newQuoteItem,
                        $quoteItem,
                        $item,
                        $processedItems
                    );

                    if (!$optionAdded) {
                        $item->setQuoteItemId($newQuoteItem->getId());
                    }
                    $this->itemQuoteRepository->save($item);
                }
                $count++;
            }
        }
    }

    /**
     * Check parent item for one item on quote
     *
     * @param Item $quoteItem
     * @param \Amasty\MultiInventory\Model\Warehouse\Quote\Item $item
     * @param array $processedItems
     */
    private function checkParentItem($item, $quoteItem, &$processedItems)
    {
        if ($quoteItem->getParentItem()) {
            $quoteItem->getParentItem()->setQty($item->getQty());
            $processedItems[] = $quoteItem->getParentItemId();
        } else {
            $quoteItem->setQty($item->getQty());
        }
    }

    /**
     * @param Item $newQuoteItem
     * @param Item $quoteItem
     * @param \Amasty\MultiInventory\Model\Warehouse\Quote\Item $item
     * @param array $processedItems
     *
     * @return bool
     * @throws \Exception
     */
    private function checkChildrensForQuoteItem($newQuoteItem, $quoteItem, $item, &$processedItems)
    {
        $optionAdded = false;

        if ($newQuoteItem->getHasChildren()) {
            /** @var Item $newQuoteChildrens */
            foreach ($newQuoteItem->getChildren() as $newQuoteChildrens) {
                $newQuoteChildrens->save();
                $processedItems[] = $newQuoteChildrens->getId();

                if ($quoteItem->getProduct()->getId() == $newQuoteChildrens->getProduct()->getId()) {
                    $item->setQuoteItemId($newQuoteChildrens->getId());
                    $optionAdded = true;
                    break;
                }
            }
        }

        return $optionAdded;
    }

    /**
     * Save Quote
     */
    private function save()
    {
        $this->cart->getQuote()->setTotalsCollectedFlag(false);
        $this->registry->unregister('finish_quote_save');
        $this->registry->register('finish_quote_save', true);
        $this->cart->getQuote()->getShippingAddress()->unsetData('cached_items_all');
        $this->cart->getCheckoutCart()->unsetData('items_collection');
        $this->cart->getQuote()->unsetData('items_collection');
        $this->cart->getCheckoutCart()->save();
    }
}
