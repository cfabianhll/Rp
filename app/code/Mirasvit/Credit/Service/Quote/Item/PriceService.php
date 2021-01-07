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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Credit\Service\Quote\Item;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item as QuoteItemResource;
use Magento\Tax\Model\Config as TaxConfig;
use Mirasvit\Credit\Api\Config\CalculationConfigInterface;

class PriceService
{
    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var QuoteItemResource
     */
    private $quoteItemResource;
    /**
     * @var CalculationConfigInterface
     */
    private $calculationConfig;
    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * PriceService constructor.
     * @param ItemFactory $itemFactory
     * @param QuoteItemResource $quoteItemResource
     * @param TaxConfig $taxConfig
     * @param CalculationConfigInterface $calculationConfig
     */
    public function __construct(
        ItemFactory $itemFactory,
        QuoteItemResource $quoteItemResource,
        TaxConfig $taxConfig,
        CalculationConfigInterface $calculationConfig
    ) {
        $this->itemFactory = $itemFactory;
        $this->quoteItemResource = $quoteItemResource;
        $this->taxConfig = $taxConfig;
        $this->calculationConfig = $calculationConfig;
    }
    /**
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @return float
     */
    public function calcItemBasePrice($item)
    {
        if (!($itemBasePrice = $this->calcBasePrice($item))) {
            return 0;
        }
        if ($this->calculationConfig->isTaxIncluded()) {
            if (!$this->taxConfig->applyTaxAfterDiscount()) {
                $itemBasePrice = $item->getBaseRowTotalInclTax() ?: $itemBasePrice + (float)$item->getData('base_tax_amount');
            }
            $itemBasePrice += $item->getBaseWeeeTaxAppliedRowAmount();
        }

        return $itemBasePrice - $item->getDiscountAmount();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @return float
     */
    public function calcItemPrice($item)
    {
        if (!($itemPrice = $this->calcPrice($item))) {
            return 0;
        }
        if ($this->calculationConfig->isTaxIncluded()) {
            if (!$this->taxConfig->applyTaxAfterDiscount()) {
                $itemPrice = $item->getRowTotalInclTax() ?: $itemPrice + (float)$item->getData('tax_amount');
            }
            $itemPrice += $item->getWeeeTaxAppliedRowAmount();
        }

        return $itemPrice - $item->getDiscountAmount();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @return float
     */
    protected function calcBasePrice($item)
    {
        $itemPrice = 0;
        $parentItem = null;
        if ($item->getParentItemId()) {
            $parentItem = $this->itemFactory->create();
            $this->quoteItemResource->load($parentItem, $item->getParentItemId());
            if (!$parentItem->getQuote()) {// "Login as Customer" failed in this place
                return $itemPrice;
            }
            $parentItem->setQuote($item->getQuote());
            $parentProduct = $parentItem->getProduct();
            if ($parentProduct->getTypeId() != BundleType::TYPE_CODE) {
                return $itemPrice;
            }
        }
        $priceIncludesTax = $this->isTaxIncluded();
        if ($parentItem) {
            $quantity = $parentItem->getQty();
        } else {
            $quantity = $item->getQty();
        }
        if ($priceIncludesTax) {
            $itemPrice = $item->getBasePriceInclTax() * $quantity;
        } else {
            $itemPrice = $item->getBasePrice() * $quantity;
        }

        return $itemPrice;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Item $item
     * @return float
     */
    protected function calcPrice($item)
    {
        $itemPrice = 0;
        $parentItem = null;
        if ($item->getParentItemId()) {
            $parentItem = $this->itemFactory->create();
            $this->quoteItemResource->load($parentItem, $item->getParentItemId());
            if (!$parentItem->getQuote()) {// "Login as Customer" failed in this place
                return $itemPrice;
            }
            $parentItem->setQuote($item->getQuote());
            $parentProduct = $parentItem->getProduct();
            if ($parentProduct->getTypeId() != BundleType::TYPE_CODE) {
                return $itemPrice;
            }
        }
        $priceIncludesTax = $this->isTaxIncluded();
        if ($parentItem) {
            $quantity = $parentItem->getQty();
        } else {
            $quantity = $item->getQty();
        }
        if ($priceIncludesTax) {
            $itemPrice = $item->getPriceInclTax() * $quantity;
        } else {
            $itemPrice = $item->getRowTotal() * $quantity;
        }

        return $itemPrice;
    }

    /**
     * @return bool
     */
    private function isTaxIncluded()
    {
        return $this->taxConfig->priceIncludesTax() || $this->calculationConfig->isTaxIncluded();
    }
}
