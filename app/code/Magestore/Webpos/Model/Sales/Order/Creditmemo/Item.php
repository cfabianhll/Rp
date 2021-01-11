<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Sales\Order\Creditmemo;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\ItemFactory;
use Magestore\Webpos\Api\Data\Sales\Order\Creditmemo\ItemExtensionInterface;
use Magestore\Webpos\Api\Data\Sales\Order\Creditmemo\ItemInterface;

/**
 * Class InvoiceService
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Item extends AbstractModel implements ItemInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_creditmemo_item';

    /**
     * @var string
     */
    protected $_eventObject = 'creditmemo_item';

    /**
     * @var Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $_orderItem = null;

    /**
     * @var ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ItemFactory $orderItemFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ItemFactory $orderItemFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * Get back to stock
     *
     * @return boolean|null
     */
    public function getBackToStock()
    {
        return $this->getData(self::BACK_TO_STOCK);
    }

    /**
     * Set back to stock
     *
     * @param boolean|null $backToStock
     * @return $this
     */
    public function setBackToStock($backToStock)
    {
        return $this->setData(self::BACK_TO_STOCK, $backToStock);
    }

    /**
     * @inheritdoc
     */
    public function getPosLocationId()
    {
        return $this->getData(self::POS_LOCATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPosLocationId($posLocationId)
    {
        return $this->setData(self::POS_LOCATION_ID, $posLocationId);
    }

    /**
     * Declare creditmemo instance
     *
     * @param Creditmemo $creditmemo
     * @return \Magento\Sales\Model\Order\Creditmemo\Item
     */
    public function setCreditmemo(Creditmemo $creditmemo)
    {
        $this->_creditmemo = $creditmemo;
        return $this;
    }

    /**
     * Retrieve creditmemo instance
     *
     * @return Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_creditmemo;
    }

    /**
     * Declare order item instance
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setOrderItem(\Magento\Sales\Model\Order\Item $item)
    {
        $this->_orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Retrieve order item instance
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        if ($this->_orderItem === null) {
            if ($this->getCreditmemo()) {
                $orderItem = $this->getCreditmemo()->getOrder()->getItemById($this->getOrderItemId());
            } else {
                $orderItem = $this->_orderItemFactory->create()->load($this->getOrderItemId());
            }
            $this->_orderItem = $orderItem;
        }
        return $this->_orderItem;
    }

    /**
     * Checks if quantity available for refund
     *
     * @param int $qty
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return bool
     */
    private function isQtyAvailable($qty, \Magento\Sales\Model\Order\Item $orderItem)
    {
        return $qty <= $orderItem->getQtyToRefund() || $orderItem->isDummy();
    }

    /**
     * Declare qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData(self::QTY, $qty);
        return $this;
    }

    /**
     * Applying qty to order item
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Item
     */
    public function register()
    {
        $orderItem = $this->getOrderItem();

        $qty = $this->processQty();
        $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $qty);
        $orderItem->setTaxRefunded($orderItem->getTaxRefunded() + $this->getTaxAmount());
        $orderItem->setBaseTaxRefunded($orderItem->getBaseTaxRefunded() + $this->getBaseTaxAmount());
        $orderItem->setDiscountTaxCompensationRefunded(
            $orderItem->getDiscountTaxCompensationRefunded() + $this->getDiscountTaxCompensationAmount()
        );
        $orderItem->setBaseDiscountTaxCompensationRefunded(
            $orderItem->getBaseDiscountTaxCompensationRefunded() + $this->getBaseDiscountTaxCompensationAmount()
        );
        $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $this->getRowTotal());
        $orderItem->setBaseAmountRefunded($orderItem->getBaseAmountRefunded() + $this->getBaseRowTotal());
        $orderItem->setDiscountRefunded($orderItem->getDiscountRefunded() + $this->getDiscountAmount());
        $orderItem->setBaseDiscountRefunded($orderItem->getBaseDiscountRefunded() + $this->getBaseDiscountAmount());

        return $this;
    }

    /**
     * Calculate qty for creditmemo item.
     *
     * @return int|float
     * @throws LocalizedException
     */
    private function processQty()
    {
        $orderItem = $this->getOrderItem();
        $qty = $this->getQty();
        if ($orderItem->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }
        $qty = $qty > 0 ? $qty : 0;
        if ($this->isQtyAvailable($qty, $orderItem)) {
            return $qty;
        } else {
            throw new LocalizedException(
                __('We found an invalid quantity to refund item "%1".', $this->getName())
            );
        }
    }

    /**
     * Cancel creaditmemeo item.
     *
     * @return $this
     */
    public function cancel()
    {
        $qty = $this->processQty();
        $this->getOrderItem()->setQtyRefunded($this->getOrderItem()->getQtyRefunded() - $qty);
        $this->getOrderItem()->setTaxRefunded(
            $this->getOrderItem()->getTaxRefunded() -
            $this->getOrderItem()->getBaseTaxAmount() *
            $qty /
            $this->getOrderItem()->getQtyOrdered()
        );
        $this->getOrderItem()->setDiscountTaxCompensationRefunded(
            $this->getOrderItem()->getDiscountTaxCompensationRefunded() -
            $this->getOrderItem()->getDiscountTaxCompensationAmount() *
            $qty /
            $this->getOrderItem()->getQtyOrdered()
        );
        return $this;
    }

    /**
     * Invoice item row total calculation
     *
     * @return $this
     */
    public function calcRowTotal()
    {
        $creditmemo = $this->getCreditmemo();
        $orderItem = $this->getOrderItem();
        $orderItemQtyInvoiced = $orderItem->getQtyInvoiced();

        $rowTotal = $orderItem->getRowInvoiced() - $orderItem->getAmountRefunded();
        $baseRowTotal = $orderItem->getBaseRowInvoiced() - $orderItem->getBaseAmountRefunded();
        $rowTotalInclTax = $orderItem->getRowTotalInclTax();
        $baseRowTotalInclTax = $orderItem->getBaseRowTotalInclTax();

        $qty = $this->processQty();
        if (!$this->isLast() && $orderItemQtyInvoiced > 0 && $qty >= 0) {
            $availableQty = $orderItemQtyInvoiced - $orderItem->getQtyRefunded();
            $rowTotal = $creditmemo->roundPrice($rowTotal / $availableQty * $qty);
            $baseRowTotal = $creditmemo->roundPrice($baseRowTotal / $availableQty * $qty, 'base');
        }
        $this->setRowTotal($rowTotal);
        $this->setBaseRowTotal($baseRowTotal);

        if ($rowTotalInclTax && $baseRowTotalInclTax) {
            $orderItemQty = $orderItem->getQtyOrdered();
            $this->setRowTotalInclTax(
                $creditmemo->roundPrice($rowTotalInclTax / $orderItemQty * $qty, 'including')
            );
            $this->setBaseRowTotalInclTax(
                $creditmemo->roundPrice($baseRowTotalInclTax / $orderItemQty * $qty, 'including_base')
            );
        }
        return $this;
    }

    /**
     * Checking if the item is last
     *
     * @return bool
     */
    public function isLast()
    {
        $orderItem = $this->getOrderItem();
        $qty = $this->processQty();
        if ((string)(double)$qty == (string)(double)$orderItem->getQtyToRefund()) {
            return true;
        }
        return false;
    }

    /**
     * Returns additional_data
     *
     * @return string|null
     */
    public function getAdditionalData()
    {
        return $this->getData(self::ADDITIONAL_DATA);
    }

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost()
    {
        return $this->getData(self::BASE_COST);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getBaseDiscountTaxCompensationAmount()
    {
        return $this->getData(self::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->getData(self::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float|null
     */
    public function getBasePriceInclTax()
    {
        return $this->getData(self::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_total
     *
     * @return float|null
     */
    public function getBaseRowTotal()
    {
        return $this->getData(self::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float|null
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(self::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(self::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float|null
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->getData(self::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float|null
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->getData(self::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float|null
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->getData(self::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float|null
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->getData(self::BASE_WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float|null
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * Returns discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(self::DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getData(self::ORDER_ITEM_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->getData(self::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float|null
     */
    public function getPriceInclTax()
    {
        return $this->getData(self::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * Returns row_total
     *
     * @return float|null
     */
    public function getRowTotal()
    {
        return $this->getData(self::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float|null
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(self::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * Returns tax_amount
     *
     * @return float|null
     */
    public function getTaxAmount()
    {
        return $this->getData(self::TAX_AMOUNT);
    }

    /**
     * Returns weee_tax_applied
     *
     * @return string|null
     */
    public function getWeeeTaxApplied()
    {
        return $this->getData(self::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float|null
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->getData(self::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float|null
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->getData(self::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float|null
     */
    public function getWeeeTaxDisposition()
    {
        return $this->getData(self::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float|null
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->getData(self::WEEE_TAX_ROW_DISPOSITION);
    }

    //@codeCoverageIgnoreStart

    /**
     * @inheritdoc
     */
    public function setParentId($id)
    {
        return $this->setData(self::PARENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setBasePrice($price)
    {
        return $this->setData(self::BASE_PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(self::TAX_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseRowTotal($amount)
    {
        return $this->setData(self::BASE_ROW_TOTAL, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setRowTotal($amount)
    {
        return $this->setData(self::ROW_TOTAL, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseDiscountAmount($amount)
    {
        return $this->setData(self::BASE_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setPriceInclTax($amount)
    {
        return $this->setData(self::PRICE_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseTaxAmount($amount)
    {
        return $this->setData(self::BASE_TAX_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBasePriceInclTax($amount)
    {
        return $this->setData(self::BASE_PRICE_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseCost($baseCost)
    {
        return $this->setData(self::BASE_COST, $baseCost);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function setBaseRowTotalInclTax($amount)
    {
        return $this->setData(self::BASE_ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setRowTotalInclTax($amount)
    {
        return $this->setData(self::ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($id)
    {
        return $this->setData(self::PRODUCT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setOrderItemId($id)
    {
        return $this->setData(self::ORDER_ITEM_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(self::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(self::DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(self::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxDisposition($weeeTaxDisposition)
    {
        return $this->setData(self::WEEE_TAX_DISPOSITION, $weeeTaxDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxRowDisposition($weeeTaxRowDisposition)
    {
        return $this->setData(self::WEEE_TAX_ROW_DISPOSITION, $weeeTaxRowDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxDisposition($baseWeeeTaxDisposition)
    {
        return $this->setData(self::BASE_WEEE_TAX_DISPOSITION, $baseWeeeTaxDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxRowDisposition($baseWeeeTaxRowDisposition)
    {
        return $this->setData(self::BASE_WEEE_TAX_ROW_DISPOSITION, $baseWeeeTaxRowDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxApplied($weeeTaxApplied)
    {
        return $this->setData(self::WEEE_TAX_APPLIED, $weeeTaxApplied);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxAppliedAmount($amount)
    {
        return $this->setData(self::BASE_WEEE_TAX_APPLIED_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxAppliedRowAmnt($amnt)
    {
        return $this->setData(self::BASE_WEEE_TAX_APPLIED_ROW_AMNT, $amnt);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxAppliedAmount($amount)
    {
        return $this->setData(self::WEEE_TAX_APPLIED_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxAppliedRowAmount($amount)
    {
        return $this->setData(self::WEEE_TAX_APPLIED_ROW_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        ItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
