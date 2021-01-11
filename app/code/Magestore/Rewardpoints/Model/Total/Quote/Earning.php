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
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Rewardpoints\Model\Total\Quote;

use Magento\Framework\Event\ObserverInterface;

/**
 * Computing Earning reward point in quote
 */
class Earning implements ObserverInterface
{
    /**
     * Action
     *
     * @var \Magestore\Rewardpoints\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magestore\Rewardpoints\Helper\Calculation\Earning
     */
    protected $_helperEarning;
    /**
     * @var \Magestore\Rewardpoints\Helper\Calculation\Spending
     */
    protected $_helperSpending;
    /**
     * @var \Magestore\Rewardpoints\Helper\Calculator
     */
    protected $_helperCalculator;
    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Earning constructor.
     *
     * @param \Magestore\Rewardpoints\Helper\Data $helper
     * @param \Magestore\Rewardpoints\Helper\Calculation\Earning $helperEarning
     * @param \Magestore\Rewardpoints\Helper\Calculation\Spending $helperSpending
     * @param \Magestore\Rewardpoints\Helper\Calculator $_helperCalculator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magestore\Rewardpoints\Helper\Data $helper,
        \Magestore\Rewardpoints\Helper\Calculation\Earning $helperEarning,
        \Magestore\Rewardpoints\Helper\Calculation\Spending $helperSpending,
        \Magestore\Rewardpoints\Helper\Calculator $_helperCalculator,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_helper = $helper;
        $this->_helperEarning = $helperEarning;
        $this->_helperSpending = $helperSpending;
        $this->_helperCalculator = $_helperCalculator;
        $this->_eventManager = $eventManager;
    }

    /**
     * Change collect total to Event to ensure earning is last runned total
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer['quote'];
        foreach ($quote->getAllAddresses() as $address) {
            if (!$quote->isVirtual() && $address->getAddressType() == 'billing') {
                continue;
            }
            if ($quote->isVirtual() && $address->getAddressType() == 'shipping') {
                continue;
            }
            $this->setEarningPoints($address, $quote);
        }
    }

    /**
     * Collect reward points that customer earned (per each item and address) total
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function setEarningPoints($address, $quote)
    {
        if (!$this->_helper->isEnable($quote->getStoreId())) {
            return $this;
        }
        if ($this->_helperSpending->getTotalPointSpent()
            && !$this->_helper->getEarningConfig('earn_when_spend', $address->getStoreId())) {
            $address->setRewardpointsEarn(0);
            return $this;
        }
        // Get points that customer can earned by Rates
        $this->_eventManager->dispatch(
            'rewardpoints_collect_earning_total_points_before',
            ['address' => $address]
        );
        $earningPoints = 0;
        if (!$address->getRewardpointsEarn()) {
            $baseGrandTotal = $this->modifyTotalToEarnPoints($address);

            if ($baseGrandTotal > 0) {
                $earningPoints = $this->_helperEarning->getRateEarningPoints(
                    $baseGrandTotal,
                    $address->getStoreId()
                );
            }
            if ($earningPoints > 0) {
                $address->setRewardpointsEarn($earningPoints);
                $quote->setRewardpointsEarn($quote->getRewardpointsEarn() + $earningPoints);
            }

            // Update earning point for each items
            $this->_updateEarningPoints($address);
        }
        $this->_eventManager->dispatch(
            'rewardpoints_collect_earning_total_points_after',
            ['address' => $address]
        );
        return $this;
    }

    /**
     * Get list type id of products which are not able to earn point
     *
     * @return array
     */
    public function getIgnoringProducts()
    {
        return [];
    }

    /**
     * Update total to calculate earning points
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return float
     */
    public function modifyTotalToEarnPoints($address)
    {
        $baseGrandTotal = $address->getBaseGrandTotal();
        $totalSubtractedTax = 0;
        /** @var \Magento\Quote\Model\Quote\Address\Item $item */
        foreach ($address->getAllVisibleItems() as $item) {
            if (in_array($item->getProductType(), $this->getIgnoringProducts())) {
                $itemTotal = $item->getQty() * $item->getBasePriceInclTax()
                    - $item->getBaseDiscountAmount();
                $baseGrandTotal = $baseGrandTotal - $itemTotal;
                $totalSubtractedTax += $item->getBaseTaxAmount();
            }
        }
        if (!$this->_helper->getEarningConfig('by_shipping', $address->getStoreId())) {
            $baseGrandTotal -= $address->getBaseShippingAmount();
            if ($this->_helper->getEarningConfig('by_tax', $address->getStoreId())) {
                $baseGrandTotal -= $address->getBaseShippingTaxAmount();
            }
        }
        if (!$this->_helper->getEarningConfig('by_tax', $address->getStoreId())) {
            $baseGrandTotal -= ($address->getBaseTaxAmount() - $totalSubtractedTax);
        }
        $baseGrandTotal = max(0, $baseGrandTotal);
        return $baseGrandTotal;
    }

    /**
     * Update earning points for address items
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function _updateEarningPoints($address)
    {
        $items = $address->getAllItems();
        $earningPoints = $address->getRewardpointsEarn();
        if (!count($items) || $earningPoints <= 0) {
            return $this;
        }

        // Calculate total item prices
        $baseItemsPrice = 0;
        $totalItemsQty = 0;
        $isBaseOnQty = false;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if (in_array($item->getProductType(), $this->getIgnoringProducts())) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax())
                        - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                    $totalItemsQty += $item->getQty() * $child->getQty();
                }
            } elseif ($item->getProduct()) {
                $baseItemsPrice += $item->getQty() * $item->getBasePriceInclTax()
                    - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                $totalItemsQty += $item->getQty();
            }
        }
        $earnpointsForShipping = $this->_helper->getEarningConfig(
            'by_shipping',
            $address->getQuote()->getStoreId()
        );
        if ($earnpointsForShipping) {
            $baseItemsPrice += $address->getBaseShippingAmount()
                + $address->getBaseShippingTaxAmount()
                - $address->getMagestoreBaseDiscountForShipping();
        }
        if ($baseItemsPrice < 0.0001) {
            $isBaseOnQty = true;
        }

        // Update for items
        $deltaRound = 0;
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if (in_array($item->getProductType(), $this->getIgnoringProducts())) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $baseItemPrice = $item->getQty() * ($child->getQty() * $child->getBasePriceInclTax())
                        - $child->getBaseDiscountAmount() - $child->getMagestoreBaseDiscount();
                    $itemQty = $item->getQty() * $child->getQty();
                    if ($isBaseOnQty) {
                        $realItemEarning = $itemQty * $earningPoints / $totalItemsQty + $deltaRound;
                    } else {
                        $realItemEarning = $baseItemPrice * $earningPoints / $baseItemsPrice + $deltaRound;
                    }
                    $itemEarning = $this->_helperCalculator->round($realItemEarning);
                    $deltaRound = $realItemEarning - $itemEarning;
                    $child->setRewardpointsEarn($itemEarning);
                }
            } elseif ($item->getProduct()) {
                $baseItemPrice = $item->getQty() * $item->getBasePriceInclTax()
                    - $item->getBaseDiscountAmount() - $item->getMagestoreBaseDiscount();
                $itemQty = $item->getQty();
                if ($isBaseOnQty) {
                    $realItemEarning = $itemQty * $earningPoints / $totalItemsQty + $deltaRound;
                } else {
                    $realItemEarning = $baseItemPrice * $earningPoints / $baseItemsPrice + $deltaRound;
                }
                $itemEarning = $this->_helperCalculator->round($realItemEarning);
                $deltaRound = $realItemEarning - $itemEarning;
                $item->setRewardpointsEarn($itemEarning);
            }
        }
        return $this;
    }
}
