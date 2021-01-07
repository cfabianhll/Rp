<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Plugin\Shipping\Model;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Helper\Cart;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ShippingFactory;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Shipping
 */
class Shipping
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\Cart
     */
    private $cart;

    /**
     * @var System
     */
    private $system;

    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repostiory;

    /**
     * @var Cart
     */
    private $helperCart;

    /**
     * @var ShippingFactory
     */
    private $whShipping;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Manager
     */
    private $manager;

    /**
     * Shipping constructor.
     *
     * @param Warehouse\Cart $cart
     * @param WarehouseFactory $factory
     * @param WarehouseRepositoryInterface $repository
     * @param System $system
     * @param Cart $helperCart
     * @param Registry $registry
     * @param ShippingFactory $whShipping
     * @param ScopeConfigInterface $scopeConfig
     * @param ManagerInterface $messageManager
     * @param Manager $manager
     */
    public function __construct(
        \Amasty\MultiInventory\Model\Warehouse\Cart $cart,
        WarehouseFactory $factory,
        WarehouseRepositoryInterface $repository,
        System $system,
        Cart $helperCart,
        Registry $registry,
        ShippingFactory $whShipping,
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        Manager $manager
    ) {
        $this->cart = $cart;
        $this->factory = $factory;
        $this->repostiory = $repository;
        $this->system = $system;
        $this->helperCart = $helperCart;
        $this->whShipping = $whShipping;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->registry = $registry;
        $this->manager = $manager;
    }

    /**
     * Separate rates, if some wearehouses
     *
     * @param \Magento\Shipping\Model\Shipping $shipping
     * @param \Closure $work
     * @param RateRequest $request
     *
     * @return \Magento\Shipping\Model\Shipping
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundCollectRates(
        \Magento\Shipping\Model\Shipping $shipping,
        \Closure $work,
        RateRequest $request
    ) {
        if (!$this->system->isMultiEnabled() || !$this->system->getDefinationWarehouse()) {
            return $work($request);
        }
        $oldQuoteItems = [];
        $quoteItem = current($request->getAllItems());

        if ($quoteItem instanceof Item) {
            $this->cart->setQuote($quoteItem->getQuote());
        }
        $this->updateFromRegistry($oldQuoteItems);
        $warehouses = $this->cart->getWarehouses();
        $result = $forShipResult = [];

        foreach ($warehouses as $warehouseId) {
            $warehouse = $this->repostiory->getById($warehouseId);
            $request = $this->helperCart->changeRequestAddress(
                $request,
                $warehouse->getData()
            );
            $groupItems = $this->updateItemsFromWarehouse($warehouseId);
            $request = $this->helperCart->changeRequestItems($request, $groupItems, $this->cart->getQuote());
            $shipment = $this->shipmentCalculate($request, $work);

            if ($warehouse->isShipping()) {
                $shipment = $this->helperCart->changePrice($shipment, $warehouse);
            }
            $result[$warehouseId] = $shipment;
            $this->updateShipmentMethods($shipment, $warehouseId, $forShipResult);
        }
        $this->registry->unregister('amasty_quote_methods');
        $this->registry->register('amasty_quote_methods', $forShipResult);
        $shipping->getResult()->append($this->helperCart->sumShipping($result));

        foreach ($oldQuoteItems as $key => $qty) {
            $quoteItem = $this->cart->getQuote()->getItemById($key);

            if ($quoteItem) {
                $quoteItem->setData(Item::KEY_QTY, $qty);
            }
        }
        $this->registry->unregister('finish_quote_save');
        $this->registry->register('finish_quote_save', false);

        return $shipping;
    }

    /**
     * @param array $oldQuoteItems
     *
     * @throws LocalizedException
     * @throws CouldNotDeleteException
     */
    private function updateFromRegistry(&$oldQuoteItems)
    {
        if ($this->registry->registry('finish_quote_save') !== true) {
            $this->cart->addWhToItems();
            /** @var Item $item */
            foreach ($this->cart->getQuote()->getItemsCollection()->getItems() as $item) {
                if ($item->getId()) {
                    $oldQuoteItems[$item->getId()] = $item->getQty();
                }
            }
        }
    }

    /**
     * @param int $warehouseId
     *
     * @return array
     * @throws LocalizedException
     */
    private function updateItemsFromWarehouse($warehouseId)
    {
        // get all cart items of warehouse
        $items = $this->cart->getGroupItems($warehouseId);
        $groupItems = $addedParents = [];

        foreach ($items as $item) {
            $quoteItem = $this->cart->getQuote()->getItemById($item['quote_item_id']);

            if ($this->registry->registry('finish_quote_save') !== true) {
                $quoteItem->setData(Item::KEY_QTY, $item['qty']);
            }
            $parentId = $quoteItem->getParentItemId();

            if ($parentId) {
                $parentItem = $this->cart->getQuote()->getItemById($quoteItem->getParentItemId());

                if (!in_array($parentId, $addedParents)) {
                    $addedParents[] = $parentId;
                    $groupItems[] = $parentItem;
                }

                if ($parentItem->getProductType() == 'bundle') {
                    continue;
                }
            }
            $groupItems[] = $quoteItem;
        }

        return $groupItems;
    }

    /**
     * @param Result $shipment
     * @param int $warehouseId
     * @param array $forShipResult
     */
    public function updateShipmentMethods($shipment, $warehouseId, &$forShipResult)
    {
        $methods = [];

        foreach ($shipment->getAllRates() as $resultMethod) {
            $methods[] = [
                'method'       => $resultMethod->getMethod(),
                'carrier_code' => $resultMethod->getCarrier(),
                'price'        => $resultMethod->getPrice()
            ];
        }
        $forShipResult[$warehouseId] = $methods;
    }

    /**
     * @param RateRequest $request
     * @param \Closure $work
     *
     * @return Result
     */
    private function shipmentCalculate(RateRequest $request, \Closure $work)
    {
        /** @var \Amasty\MultiInventory\Model\Shipping $whShipping */
        $whShipping = $this->whShipping->create();
        $limitCarrier = $request->getLimitCarrier();
        $storeId = $request->getStoreId();

        if (!$limitCarrier) {
            if (!$this->manager->isEnabled('Amasty_Shiprules')) {
                foreach ($this->getCarriers($storeId) as $carrierCode => $carrierConfig) {
                    $whShipping->collectCarrierRates($carrierCode, $request);
                }
            } elseif (!$this->registry->registry('is_shipping_rules')) {
                $work($request);
                $this->registry->register('is_shipping_rules', true);
            }
        } else {
            if (!is_array($limitCarrier)) {
                $limitCarrier = [$limitCarrier];
            }
            foreach ($limitCarrier as $carrierCode) {
                $carrierConfig = $this->getCarriers($storeId, $carrierCode);
                if (!$carrierConfig) {
                    continue;
                }

                $whShipping->collectCarrierRates($carrierCode, $request);
            }
        }

        return $whShipping->getResult();
    }

    /**
     * @param int $storeId
     * @param string|null $carrierCode
     *
     * @return array
     */
    private function getCarriers($storeId, $carrierCode = null)
    {
        $configPath = 'carriers';
        if ($carrierCode !== null) {
            $configPath .= '/' . $carrierCode;
        }
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
