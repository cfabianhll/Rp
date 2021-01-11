<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Plugin\Sales\Model;

/**
 * Custom Order class for webpos
 */
class Order
{
    const STATE_CLOSED = 'closed';

    const ACTION_FLAG_EDIT = 'edit';

    /**
     * Custom cancreditmemo function to use custom CanCreditmemoForZeroTotal function
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundCanCreditmemo(
        \Magento\Sales\Model\Order $subject,
        callable $proceed
    ) {
        $url = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\UrlInterface::class);
        $currentUrl = $url->getCurrentUrl();
        $pattern = '/\/V1\/webpos\//';
        preg_match($pattern, $currentUrl, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) <= 0) {
            return $proceed();
        }

        if ($subject->hasForcedCanCreditmemo()) {
            return $subject->getForcedCanCreditmemo();
        }

        if ($subject->canUnhold()
            || $subject->isPaymentReview()
            || $subject->isCanceled()
            || $subject->getState() === self::STATE_CLOSED
        ) {
            return false;
        }

        /**
         * We can have problem with float in php (on some server $a=762.73;$b=762.73; $a-$b!=0)
         * for this we have additional diapason for 0
         * TotalPaid - contains amount, that were not rounded.
         */
        $customPriceCurrency = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $totalRefunded = $customPriceCurrency->round($subject->getTotalPaid()) - $subject->getTotalRefunded();
        if (abs($subject->getGrandTotal()) < .0001) {
            return $this->customCanCreditmemoForZeroTotal($totalRefunded, $subject);
        }

        return $this->customCanCreditmemoForZeroTotalRefunded($totalRefunded, $subject);
    }

    /**
     * Custom Retrieve credit memo for zero total availability.
     *
     * Fix issue not change order status from complete to close when refund order with total = 0
     *
     * @param float $totalRefunded
     * @param \Magento\Sales\Model\Order $subject
     * @return bool
     */
    private function customCanCreditmemoForZeroTotal(float $totalRefunded, \Magento\Sales\Model\Order $subject): bool
    {
        $customMemoCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\CollectionFactory::class);

        $totalPaid = $subject->getTotalPaid();
        //check if total paid is less than grand total
        $checkAmtTotalPaid = $totalPaid <= $subject->getGrandTotal();
        //case when amount is due for invoice
        $hasDueAmount = $subject->canInvoice() && $checkAmtTotalPaid;
        //case when paid amount is refunded and order has creditmemo created
        $existCreditmemos = ($subject->getCreditmemosCollection() === false) ?
            true : ($customMemoCollectionFactory->create()->setOrderFilter($subject)->getTotalCount() > 0);

        $orderItemsQty = 0;
        $orderRefundedQty = 0;
        foreach ($subject->getItems() as $item) {
            $orderItemsQty += $item->getQtyOrdered();
            $orderRefundedQty += $item->getQtyRefunded();
        }
        $paidAmtIsRefunded = $existCreditmemos && $orderRefundedQty == $orderItemsQty;

        if (($hasDueAmount || $paidAmtIsRefunded)
            || (!$checkAmtTotalPaid && abs($totalRefunded - $subject->getAdjustmentNegative()) < .0001)
        ) {
            return false;
        }
        return true;
    }
    /**
     * Retrieve credit memo for zero total refunded availability.
     *
     * @param float $totalRefunded
     * @param \Magento\Sales\Model\Order $subject
     * @return bool
     */
    private function customCanCreditmemoForZeroTotalRefunded(
        float $totalRefunded,
        \Magento\Sales\Model\Order $subject
    ) {
        $isRefundZero = abs($totalRefunded) < .0001;
        // Case when Adjustment Fee (adjustment_negative) has been used for first creditmemo
        $hasAdjustmentFee = abs($totalRefunded - $subject->getAdjustmentNegative()) < .0001;
        $hasActionFlag = $subject->getActionFlag(self::ACTION_FLAG_EDIT) === false;
        if ($isRefundZero || $hasAdjustmentFee || $hasActionFlag) {
            return false;
        }

        return true;
    }
}
