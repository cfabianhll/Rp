<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model\Service;

use Mirasvit\Rma\Helper\Serializer;

/**
 * Quote submit service model.
 */
class Order
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Order constructor.
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * Order object.
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * Quote convert object.
     *
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $converter;

    /**
     * Quote convertor declaration.
     *
     * @param \Magento\Sales\Model\Convert\Order $converter
     *
     * @return Order
     */
    public function setConverter(\Magento\Sales\Model\Convert\Order $converter)
    {
        $this->converter = $converter;

        return $this;
    }

    /**
     * Get assigned order object.
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Updates numeric data taking into account locale.
     *
     * @param array $data
     *
     * @return Order
     */
    public function updateLocaleNumbers(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $data[$key] = $this->_getLocaleNumber($value);
                }
            }
        }

        return $this;
    }

    /**
     * Perform numbers conversion according to locale.
     *
     * @param string $value
     *
     * @return float
     */
    protected function _getLocaleNumber($value)
    {
        return $this->localeFormat->getNumber($value);
    }

    /**
     * Prepare order invoice based on order data and requested items qtys. If $qtys is not empty - the function will
     * prepare only specified items, otherwise all containing in the order.
     *
     * @param array $qtys
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function prepareInvoice($qtys = [])
    {
        $this->updateLocaleNumbers($qtys);
        $invoice = $this->converter->toInvoice($this->order);
        $totalQty = 0;
        foreach ($this->order->getAllItems() as $orderItem) {
            if (!$this->_canInvoiceItem($orderItem, [])) {
                continue;
            }
            $item = $this->converter->itemToInvoiceItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (!empty($qtys)) {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float)$qtys[$orderItem->getId()];
                }
            } else {
                $qty = $orderItem->getQtyToInvoice();
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $this->order->getInvoiceCollection()->addItem($invoice);

        return $invoice;
    }

    /**
     * Prepare order shipment based on order items and requested items qty.
     *
     * @param array $qtys
     *
     * @return \Magento\Sales\Model\Order\Shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Json_Exception
     */
    public function prepareShipment($qtys = [])
    {
        $this->updateLocaleNumbers($qtys);
        $totalQty = 0;
        $shipment = $this->converter->toShipment($this->order);
        foreach ($this->order->getAllItems() as $orderItem) {
            if (!$this->_canShipItem($orderItem, $qtys)) {
                continue;
            }

            $item = $this->converter->itemToShipmentItem($orderItem);

            if ($orderItem->isDummy(true)) {
                $qty = 0;
                if (isset($qtys[$orderItem->getParentItemId()])) {
                    $productOptions = $orderItem->getProductOptions();
                    if (isset($productOptions['bundle_selection_attributes'])) {
                        $bundleSelectionAttributes = $this->serializer
                            ->unserialize($productOptions['bundle_selection_attributes']);

                        if ($bundleSelectionAttributes) {
                            $qty = $bundleSelectionAttributes['qty'] * $qtys[$orderItem->getParentItemId()];
                            $qty = min($qty, $orderItem->getSimpleQtyToShip());

                            $item->setQty($qty);
                            $shipment->addItem($item);
                            continue;
                        } else {
                            $qty = 1;
                        }
                    }
                } else {
                    $qty = 1;
                }
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = min($qtys[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $shipment->addItem($item);
        }

        $shipment->setTotalQty($totalQty);

        return $shipment;
    }

    /**
     * Prepare order creditmemo based on order items and requested params.
     *
     * @param array $data
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function prepareCreditmemo($data = [])
    {
        $totalQty = 0;
        $creditmemo = $this->converter->toCreditmemo($this->order);
        $qtys = isset($data['qtys']) ? $data['qtys'] : [];
        $this->updateLocaleNumbers($qtys);

        foreach ($this->order->getAllItems() as $orderItem) {
            if (!$this->_canRefundItem($orderItem, $qtys)) {
                continue;
            }
            $item = $this->converter->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = 1;
                $orderItem->setLockedDoShip(1);
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float)$qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->_initCreditmemoData($creditmemo, $data);

        $creditmemo->collectTotals();

        return $creditmemo;
    }

    /**
     * Prepare order creditmemo based on invoice items and requested requested params.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param array                              $data
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function prepareInvoiceCreditmemo($invoice, $data = [])
    {
        $totalQty = 0;
        $qtys = isset($data['qtys']) ? $data['qtys'] : [];
        $this->updateLocaleNumbers($qtys);

        $creditmemo = $this->converter->toCreditmemo($this->order);
        $creditmemo->setInvoice($invoice);

        $invoiceQtysRefunded = [];
        foreach ($invoice->getOrder()->getCreditmemosCollection() as $createdCreditmemo) {
            if ($createdCreditmemo->getState() != \Magento\Sales\Model\Order\Creditmemo::STATE_CANCELED
                && $createdCreditmemo->getInvoiceId() == $invoice->getId()
            ) {
                foreach ($createdCreditmemo->getAllItems() as $createdCreditmemoItem) {
                    $orderItemId = $createdCreditmemoItem->getOrderItem()->getId();
                    if (isset($invoiceQtysRefunded[$orderItemId])) {
                        $invoiceQtysRefunded[$orderItemId] += $createdCreditmemoItem->getQty();
                    } else {
                        $invoiceQtysRefunded[$orderItemId] = $createdCreditmemoItem->getQty();
                    }
                }
            }
        }

        $invoiceQtysRefundLimits = [];
        foreach ($invoice->getAllItems() as $invoiceItem) {
            $invoiceQtyCanBeRefunded = $invoiceItem->getQty();
            $orderItemId = $invoiceItem->getOrderItem()->getId();
            if (isset($invoiceQtysRefunded[$orderItemId])) {
                $invoiceQtyCanBeRefunded = $invoiceQtyCanBeRefunded - $invoiceQtysRefunded[$orderItemId];
            }
            $invoiceQtysRefundLimits[$orderItemId] = $invoiceQtyCanBeRefunded;
        }

        foreach ($invoice->getAllItems() as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();

            if (!$this->_canRefundItem($orderItem, $qtys, $invoiceQtysRefundLimits)) {
                continue;
            }

            $item = $this->converter->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = 1;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float)$qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
                if (isset($invoiceQtysRefundLimits[$orderItem->getId()])) {
                    $qty = min($qty, $invoiceQtysRefundLimits[$orderItem->getId()]);
                }
            }
            $qty = min($qty, $invoiceItem->getQty());
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->_initCreditmemoData($creditmemo, $data);
        if (!isset($data['shipping_amount'])) {
            $order = $invoice->getOrder();
            $isShippingInclTax = $this->config->displaySalesShippingInclTax($order->getStoreId());
            if ($isShippingInclTax) {
                $baseAllowedAmount = $order->getBaseShippingInclTax()
                    - $order->getBaseShippingRefunded()
                    - $order->getBaseShippingTaxRefunded();
            } else {
                $baseAllowedAmount = $order->getBaseShippingAmount() - $order->getBaseShippingRefunded();
                $baseAllowedAmount = min($baseAllowedAmount, $invoice->getBaseShippingAmount());
            }
            $creditmemo->setBaseShippingAmount($baseAllowedAmount);
        }

        $creditmemo->collectTotals();

        return $creditmemo;
    }

    /**
     * Initialize creditmemo state based on requested parameters.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @param array                                 $data
     * @return void
     */
    protected function _initCreditmemoData($creditmemo, $data)
    {
        $this->updateLocaleNumbers($data);
        if (isset($data['shipping_amount'])) {
            $creditmemo->setBaseShippingAmount((float)$data['shipping_amount']);
        }

        if (isset($data['adjustment_positive'])) {
            $creditmemo->setAdjustmentPositive($data['adjustment_positive']);
        }

        if (isset($data['adjustment_negative'])) {
            $creditmemo->setAdjustmentNegative($data['adjustment_negative']);
        }
    }

    /**
     * Check if order item can be invoiced. Dummy item can be invoiced or with his childrens or
     * with parent item which is included to invoice.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $qtys
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _canInvoiceItem($item, $qtys = [])
    {
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        $this->updateLocaleNumbers($qtys);

        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }

                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }

    /**
     * Check if order item can be shiped. Dummy item can be shiped or with his childrens or
     * with parent item which is included to shipment.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $qtys
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _canShipItem($item, $qtys = [])
    {
        if ($item->getIsVirtual() || $item->getLockedDoShip()) {
            return false;
        }
        $this->updateLocaleNumbers($qtys);

        if ($item->isDummy(true)) {
            if ($item->getHasChildren()) {
                if ($item->isShipSeparately()) {
                    return true;
                }
                foreach ($item->getChildrenItems() as $child) {
                    if ($child->getIsVirtual()) {
                        continue;
                    }
                    if (empty($qtys)) {
                        if ($child->getQtyToShip() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }

                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToShip() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToShip() > 0;
        }
    }

    /**
     * Check if order item can be refunded.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $qtys
     * @param array                           $invoiceQtysRefundLimits
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _canRefundItem($item, $qtys = [], $invoiceQtysRefundLimits = [])
    {
        $this->updateLocaleNumbers($qtys);
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($this->_canRefundNoDummyItem($child, $invoiceQtysRefundLimits)) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }

                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $this->_canRefundNoDummyItem($parent, $invoiceQtysRefundLimits);
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $this->_canRefundNoDummyItem($item, $invoiceQtysRefundLimits);
        }
    }

    /**
     * Check if no dummy order item can be refunded.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $invoiceQtysRefundLimits
     *
     * @return bool
     */
    protected function _canRefundNoDummyItem($item, $invoiceQtysRefundLimits = [])
    {
        if ($item->getQtyToRefund() < 0) {
            return false;
        }

        if (isset($invoiceQtysRefundLimits[$item->getId()])) {
            return $invoiceQtysRefundLimits[$item->getId()] > 0;
        }

        return true;
    }
}
