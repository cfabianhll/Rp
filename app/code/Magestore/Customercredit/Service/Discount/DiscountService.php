<?php
/**
 * Magestore
 * NOTICE OF LICENSE
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Customercredit\Service\Discount;

use Magento\Framework\App\ObjectManager;

/**
 * Service discount by store credit
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DiscountService
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var array
     */
    protected $quoteTotalData;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * DiscountService constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Tax\Model\Config|null $taxConfig
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Tax\Model\Config $taxConfig = null
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->taxConfig = $taxConfig ?: ObjectManager::getInstance()->create(\Magento\Tax\Model\Config::class);
    }

    /**
     * Calculate quote totals for each giftCode and save results
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @param bool $isApplyGiftAfterTax
     */
    public function initTotals($items, $isApplyGiftAfterTax = false)
    {
        $totalItemsPrice = 0;
        $totalBaseItemsPrice = 0;
        $validItemsCount = 0;
        foreach ($items as $item) {
            //Skipping child items to avoid double calculations
            if ($item->getParentItemId() && $item->getProduct()->getTypeId() == 'customercredit') {
                continue;
            }

            $qty = $item->getTotalQty();
            $totalItemsPrice += $this->getItemPrice($item) * $qty - $item->getDiscountAmount();
            $totalBaseItemsPrice += $this->getItemBasePrice($item) * $qty - $item->getBaseDiscountAmount();
            if ($isApplyGiftAfterTax && !$this->taxConfig->discountTax()) {
                $totalItemsPrice += $item->getTaxAmount();
                $totalBaseItemsPrice += $item->getBaseTaxAmount();
            } elseif (!$isApplyGiftAfterTax && $this->taxConfig->discountTax()) {
                $totalItemsPrice -= $item->getTaxAmount();
                $totalBaseItemsPrice -= $item->getBaseTaxAmount();
            }
            $validItemsCount++;
        }

        $this->quoteTotalData = [
            'items_price' => $totalItemsPrice,
            'base_items_price' => $totalBaseItemsPrice,
            'items_count' => $validItemsCount,
        ];
    }

    /**
     * Get Quote Total Data
     *
     * @return array
     */
    public function getQuoteTotalData()
    {
        return $this->quoteTotalData;
    }

    /**
     * Return item base price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemBasePrice($item)
    {
        $price = $item->getBaseDiscountCalculationPrice();
        if ($price === null) {
            return $item->getBaseCalculationPrice();
        } else {
            return $price;
        }
    }

    /**
     * Return item price
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return float
     */
    public function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        if ($price === null) {
            return $item->getCalculationPrice();
        } else {
            return $price;
        }
    }
}
