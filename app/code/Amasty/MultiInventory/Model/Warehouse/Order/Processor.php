<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Order;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Dispatch;
use Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator;
use Amasty\MultiInventory\Model\Warehouse\Item\ValidatorResultData;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Processor
 */
class Processor
{
    /**
     * Array keys for changeDataOrder data array
     */
    const TOTAL_QTY = 'total_qty';

    const SUB_TOTAL = 'sub_total';

    const BASE_SUB_TOTAL = 'base_sub_total';

    const SUB_TOTAL_INCL_TAX = 'sub_total_incl_tax';

    const BASE_SUB_TOTAL_INCL_TAX = 'base_sub_total_incl_tax';

    const DISCOUNT = 'discount';

    const BASE_DISCOUNT = 'base_discount';

    const TAX = 'tax';

    const BASE_TAX = 'base_tax';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var System
     */
    private $system;

    /**
     * @var QuantityValidator
     */
    private $quantityValidator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    public function __construct(
        Data $helper,
        System $system,
        QuantityValidator $quantityValidator,
        OrderRepositoryInterface $orderRepository,
        ItemFactory $orderItemFactory,
        OrderItemRepositoryInterface $orderItemRepository,
        PriceCurrencyInterface $priceCurrency,
        Registry $registry,
        OrderFactory $orderFactory
    ) {
        $this->helper = $helper;
        $this->system = $system;
        $this->quantityValidator = $quantityValidator;
        $this->orderRepository = $orderRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->priceCurrency = $priceCurrency;
        $this->registry = $registry;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Get warehouse for order
     *
     * @param Order $order
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function dispatchWarehouse($order)
    {
        $this->helper->getDispatch()->setCallables($this->system->getDispatchOrder());
        $this->helper->getDispatch()->resetExclude();
        $this->helper->getDispatch()->setDirection(Dispatch::DIRECTION_ORDER);

        $results = [];

        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getParentItemId()
                || $this->quantityValidator->isProductSimple($orderItem->getProduct())
            ) {
                $this->helper->getDispatch()->setOrderItem($orderItem);
                $validationResult = $this->quantityValidator
                    ->checkQuoteItemQty($orderItem, $orderItem->getQtyOrdered());

                $itemResults = $this->checkOrderItem($validationResult, $orderItem);
                if (isset($itemResults[0])) {
                    $results[] = $itemResults[0];
                }
            }
        }

        return $results;
    }

    /**
     * Separate orders on warehouses
     *
     * @param $result
     * @param Order $order
     *
     * @return array
     */
    public function separateOrders($result, $order)
    {
        if (!$this->system->getSeparateOrders()) {
            return [$result, [$order]];
        }
        $list = $this->prepareEntityList($result);

        if (count($list) <= 1) {
            return [$result, [$order]];
        }
        $orders = [];
        $numberOrder = 1;
        $baseShippingAmount = $order->getBaseShippingAmount()
            ? round($order->getBaseShippingAmount() / count($list), 4) : null;

        foreach ($list as $wh => $items) {
            if ($numberOrder > 1) {
                $newOrder = $this->orderFactory->create();
                $newOrder->setData($this->beforeDataOrder($order->getData()));
                $this->preparePaymentsAndAddresses($order, $newOrder);

                /** Save state and status value for next save to leave order pending */
                $state = $newOrder->getState();
                $status = $newOrder->getStatus();
                $this->orderRepository->save($newOrder);

                $this->prepareOrderItems($items, $result, $newOrder);

                /** Change state from complete */
                if ($newOrder->getState() != $state || $newOrder->getStatus() != $status) {
                    $newOrder->setState($state);
                    $newOrder->setStatus($status);
                    $this->orderRepository->save($newOrder);
                }
                $order = $this->changeDataOrder(
                    $result,
                    $items,
                    $newOrder,
                    $this->setShippingAmount($newOrder, $wh, $baseShippingAmount)
                );
            } else {
                $order = $this->changeDataOrder(
                    $result,
                    $items,
                    $order,
                    $this->setShippingAmount($order, $wh, $baseShippingAmount)
                );
            }
            $orders[] = $order;
            $numberOrder++;
        }

        return [$result, $orders];
    }

    /**
     * @param array $result
     *
     * @return array
     */
    private function prepareEntityList($result)
    {
        $list = [];

        foreach ($result as $key => $itemEntity) {
            if (!isset($list[$itemEntity['warehouse_id']])) {
                $list[$itemEntity['warehouse_id']] = [];
            }
            $list[$itemEntity['warehouse_id']][] = $key;
        }

        return $list;
    }

    /**
     * @param array $items
     * @param array $result
     * @param Order $newOrder
     */
    private function prepareOrderItems($items, &$result, $newOrder)
    {
        foreach ($items as $item) {
            $orderItem = $this->orderItemRepository->get($result[$item]['order_item_id']);

            if ($orderItem->getParentItemId()) {
                $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                $parentOrderItem->setOrderId($newOrder->getId());
                $this->orderItemRepository->save($parentOrderItem);
            }
            $orderItem->setOrderId($newOrder->getId());
            $this->orderItemRepository->save($orderItem);
            $result[$item]['order_id'] = $newOrder->getId();
        }
    }

    /**
     * @param Order $order
     * @param Order $newOrder
     */
    private function preparePaymentsAndAddresses($order, $newOrder)
    {
        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setId(null);
        $payment->setParentId(null);
        $newOrder->setPayment($payment);
        $addresses = $order->getAddresses();

        foreach ($addresses as $address) {
            $address->setId(null);
            $address->setParentId(null);
            $newOrder->addAddress($address);
        }
    }

    /**
     * If you do not have enough products in warehouse, we take the other
     *
     * @param ValidatorResultData[] $result
     * @param \Magento\Sales\Model\Order\Item $orderItem
     *
     * @return array
     */
    private function checkOrderItem($result, $orderItem)
    {
        $arrayResults = [];
        foreach ($result as $validatorResult) {

            if ($validatorResult->getIsSplitted()) {
                $newItem = $this->createOrderItem($orderItem, $validatorResult->getQty(), $orderItem->getOrder());
                $validatorResult->setOrderItemId($newItem->getItemId());
                $arrayResults[] = $validatorResult->getData();
                continue;
            }
            if ($validatorResult->getIsChanged()) {
                $qty = $validatorResult->getQty();
                if ($orderItem->getParentItemId()) {
                    $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                    $parentOrderItem->setQtyOrdered($qty);
                    $parentOrderItem = $this->changeTotal($parentOrderItem, $qty);
                    $this->orderItemRepository->save($parentOrderItem);
                    $orderItem->setParentItem($parentOrderItem);
                }
                $orderItem->setQtyOrdered($qty);
                if ($orderItem->getPrice() > 0) {
                    $orderItem = $this->changeTotal($orderItem, $qty);
                }
                $this->orderItemRepository->save($orderItem);
            }
            $arrayResults[] = $validatorResult->getData();
        }

        return $arrayResults;
    }

    /**
     * add Items in Order
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param int $qty
     * @param Order $order
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    private function createOrderItem($orderItem, $qty, $order)
    {
        $parent = 0;

        if ($orderItem->getParentItemId()) {
            $newParentOrderItem = $this->orderItemFactory->create();
            $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
            $newParentOrderItem->setData($this->beforeData($parentOrderItem->getData()));
            $newParentOrderItem->setQtyOrdered($qty);
            $newParentOrderItem = $this->changeTotal($newParentOrderItem, $qty);
            $this->orderItemRepository->save($newParentOrderItem);
            $parent = $newParentOrderItem->getId();
            $order->addItem($newParentOrderItem);
        }
        $newOrderItem = $this->orderItemFactory->create();
        $newOrderItem->setData($this->beforeData($orderItem->getData()));
        $newOrderItem->setQtyOrdered($qty);

        if ($parent) {
            $newOrderItem->setParentItemId($parent);
            $newOrderItem->setParentItem($newParentOrderItem);
        }

        if ($newOrderItem->getPrice()) {
            $newOrderItem = $this->changeTotal($newOrderItem, $qty);
        }
        $this->orderItemRepository->save($newOrderItem);
        $order->addItem($newOrderItem);

        return $newOrderItem;
    }

    /**
     * @param Order $order
     * @param int $warehouse
     * @param float $amount
     *
     * @return float
     */
    private function setShippingAmount($order, $warehouse, $amount)
    {
        if (!empty($this->registry->registry('amasty_quote_methods'))) {
            $shippings = $this->registry->registry('amasty_quote_methods');
            $method = $order->getShippingMethod(true);
            $list = $shippings[$warehouse];

            foreach ($list as $resultMethod) {
                if ($resultMethod['method'] == $method->getMethod()
                    && $resultMethod['carrier_code'] == $method->getCarrierCode()
                ) {
                    $amount = $resultMethod['price'];
                }
            }
        }

        return $amount;
    }

    /**
     * Calc Total for New Order
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $qty
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    private function changeTotal($item, $qty)
    {
        $total = $this->priceCurrency->round($item->getPrice() * $qty);
        $baseTotal = $this->priceCurrency->round($item->getBasePrice() * $qty);
        $totalInclTax = $this->priceCurrency->round($item->getPriceInclTax() * $qty);
        $baseTotalInclTax = $this->priceCurrency->round($item->getBasePriceInclTax() * $qty);
        $item->setRowTotal($total);
        $item->setBaseRowTotal($baseTotal);
        $item->setRowTotalInclTax($totalInclTax);
        $item->setBaseRowTotalInclTax($baseTotalInclTax);

        if ($item->getDiscountPercent()) {
            $discount = $this->priceCurrency->round($total * ($item->getDiscountPercent() / 100));
            $baseDiscount = $this->priceCurrency->round($baseTotal * ($item->getDiscountPercent() / 100));
            $item->setDiscountAmount($discount);
            $item->setBaseDiscountAmount($baseDiscount);
        }

        return $item;
    }

    /**
     * Each create order after separate
     *
     * @param array $result
     * @param array $items
     * @param Order $order
     * @param float $baseShippingAmount
     *
     * @return Order
     */
    private function changeDataOrder($result, $items, $order, $baseShippingAmount)
    {
        $data = [
            self::TOTAL_QTY => 0,
            self::SUB_TOTAL => 0,
            self::BASE_SUB_TOTAL => 0,
            self::SUB_TOTAL_INCL_TAX => 0,
            self::BASE_SUB_TOTAL_INCL_TAX => 0,
            self::DISCOUNT => 0,
            self::BASE_DISCOUNT => 0,
            self::TAX => 0,
            self::BASE_TAX => 0
        ];

        foreach ($items as $item) {
            $orderItem = $this->orderItemRepository->get($result[$item]['order_item_id']);

            if ($orderItem->getParentItemId()) {
                $parentOrderItem = $this->orderItemRepository->get($orderItem->getParentItemId());
                $this->changeOrderItem($parentOrderItem, $data);
            } elseif ($orderItem->getPrice() > 0) {
                $this->changeOrderItem($orderItem, $data);
            }
        }
        $shippingAmount = $this->priceCurrency->convert($this->priceCurrency->round($baseShippingAmount));
        $this->updateOrder($order, $data, $baseShippingAmount, $shippingAmount);

        return $order;
    }

    /**
     * @param OrderItemInterface $orderItem
     * @param array $data
     */
    private function changeOrderItem($orderItem, &$data)
    {
        $data[self::TOTAL_QTY] += $orderItem->getQtyOrdered();
        $data[self::SUB_TOTAL] += $this->priceCurrency->round(
            $orderItem->getQtyOrdered() * $orderItem->getPrice()
        );
        $data[self::BASE_SUB_TOTAL] += $this->priceCurrency->round(
            $orderItem->getQtyOrdered() * $orderItem->getBasePrice()
        );
        $data[self::SUB_TOTAL_INCL_TAX] += $this->priceCurrency->round(
            $orderItem->getQtyOrdered() * $orderItem->getPriceInclTax()
        );
        $data[self::BASE_SUB_TOTAL_INCL_TAX] += $this->priceCurrency->round(
            $orderItem->getQtyOrdered() * $orderItem->getBasePriceInclTax()
        );
        if ($orderItem->getDiscountPercent()) {
            $data[self::DISCOUNT] += $this->priceCurrency->round(
                $data[self::SUB_TOTAL] * ($orderItem->getDiscountPercent() / 100)
            );
            $data[self::BASE_DISCOUNT] += $this->priceCurrency->round(
                $data[self::BASE_SUB_TOTAL] * ($orderItem->getDiscountPercent() / 100)
            );
        }
        if ($orderItem->getTaxPercent()) {
            $data[self::TAX] += $this->priceCurrency->round(
                $data[self::SUB_TOTAL] * ($orderItem->getTaxPercent() / 100)
            );
            $data[self::BASE_TAX] += $this->priceCurrency->round(
                $data[self::BASE_SUB_TOTAL] * ($orderItem->getTaxPercent() / 100)
            );
        }
    }

    /**
     * @param Order $order
     * @param array $data
     * @param float $baseShippingAmount
     * @param float $shippingAmount
     */
    private function updateOrder($order, $data, $baseShippingAmount, $shippingAmount)
    {
        $amountDiscount = $data[self::DISCOUNT];
        $baseAmountDiscount = $data[self::BASE_DISCOUNT];

        if ($data[self::DISCOUNT] > 0) {
            $amountDiscount = -$data[self::DISCOUNT];
            $baseAmountDiscount = -$data[self::BASE_DISCOUNT];
        }
        $order->setBaseDiscountAmount($baseAmountDiscount);
        $order->setDiscountAmount($amountDiscount);
        $order->setBaseTaxAmount($data[self::BASE_TAX]);
        $order->setTaxAmount($data[self::TAX]);
        $order->setBaseGrandTotal(
            $data[self::BASE_SUB_TOTAL]
            - $data[self::BASE_DISCOUNT]
            + $data[self::BASE_TAX]
            + $baseShippingAmount
        );
        $order->setGrandTotal($data[self::SUB_TOTAL] - $data[self::DISCOUNT] + $data[self::TAX] + $shippingAmount);
        $order->setBaseSubtotal($data[self::BASE_SUB_TOTAL]);
        $order->setSubtotal($data[self::SUB_TOTAL]);
        $order->setTotalQtyOrdered($data[self::TOTAL_QTY]);
        $order->setBaseSubtotalInclTax($data[self::BASE_SUB_TOTAL_INCL_TAX]);
        $order->setSubtotalInclTax($data[self::SUB_TOTAL_INCL_TAX]);
        $order->setBaseTotalDue($data[self::BASE_SUB_TOTAL] - $data[self::BASE_DISCOUNT]);
        $order->setTotalDue($data[self::SUB_TOTAL] - $data[self::DISCOUNT]);
        $order->setBaseShippingAmount($baseShippingAmount);
        $order->setBaseShippingInclTax($baseShippingAmount);
        $order->setShippingAmount($shippingAmount);
        $order->setShippingInclTax($shippingAmount);

        $this->orderRepository->save($order);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function beforeData($data)
    {
        unset($data['item_id']);
        $data['quote_item_id'] = null;
        $data['parent_item_id'] = null;

        return $data;
    }

    /**
     * @param array $data
     * @return array
     */
    private function beforeDataOrder($data)
    {
        $unsetKeys = ['entity_id', 'parent_id', 'item_id', 'order_id'];

        foreach ($unsetKeys as $key) {
            if (isset($data[$key])) {
                unset($data[$key]);
            }
        }
        $unsetKeys = ['increment_id', 'items', 'addresses', 'payment'];

        foreach ($unsetKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
