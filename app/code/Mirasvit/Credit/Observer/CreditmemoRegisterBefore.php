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



namespace Mirasvit\Credit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyHelper;

class CreditmemoRegisterBefore implements ObserverInterface
{
    /**
     * @var \Mirasvit\Credit\Helper\Calculation
     */
    protected $calculationHelper;

    /**
     * @var CurrencyHelper
     */
    protected $currencyHelper;

    /**
     * @var \Mirasvit\Credit\Helper\Data
     */
    protected $creditData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param CurrencyHelper                      $currencyHelper
     * @param \Mirasvit\Credit\Helper\Calculation $calculationHelper
     * @param \Mirasvit\Credit\Helper\Data        $creditData
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        CurrencyHelper $currencyHelper,
        \Mirasvit\Credit\Helper\Calculation $calculationHelper,
        \Mirasvit\Credit\Helper\Data $creditData,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->currencyHelper = $currencyHelper;
        $this->calculationHelper = $calculationHelper;
        $this->creditData = $creditData;
        $this->request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        $input = $this->request->getParam('creditmemo');

        $baseAmount = 0;
        if (isset($input['return_applied_credits'])) {
            $baseAmount = floatval($input['return_applied_credits']);
            if ($baseAmount > 0) {
                $baseAmount = $creditmemo->roundPrice($baseAmount);
                $refundToCreditAmount = $this->calculationHelper->convertToCurrency(
                    $baseAmount,
                    $creditmemo->getBaseCurrencyCode(),
                    $creditmemo->getOrderCurrencyCode(),
                    $creditmemo->getStore()
                );
                $refundToCreditAmount = round($refundToCreditAmount, 2, PHP_ROUND_HALF_DOWN);

                $creditDiscount = 0;
                $baseCreditDiscount = 0;
                // if return part of money as credits with full creditmemo
                if ($creditmemo->getOrder()->getBaseCreditAmount() < $baseAmount) {
                    $baseCreditDiscount = $baseAmount - $creditmemo->getOrder()->getBaseCreditAmount();
                    $creditDiscount = $refundToCreditAmount - $creditmemo->getOrder()->getCreditAmount();
                }
                // if return part of money as credits with partial creditmemo
                if (abs($creditmemo->getBaseDiscountAmount()) < $baseAmount) {
                    $baseCreditDiscount = $baseAmount - abs($creditmemo->getBaseDiscountAmount());
                    $creditDiscount = $refundToCreditAmount - abs($creditmemo->getDiscountAmount());
                }
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseCreditDiscount);
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $creditDiscount);
                if ($creditmemo->getBaseGrandTotal() <= 0) {
                    $creditmemo->setBaseGrandTotal(0);
                    $creditmemo->setGrandTotal(0);
                    $creditmemo->setAllowZeroGrandTotal(true);
                }
            } else {
                $creditmemo->setDonotReturnAppliedCredits(1);
            }
        } elseif ($creditmemo->getBaseCreditAmount() + $creditmemo->getBaseCreditReturnMax() > 0) {
            if ($creditmemo->getBaseCreditReturnMax() <= 0) {
                $baseAmount = $creditmemo->getBaseCreditAmount();
            } else {
                $baseAmount = min($creditmemo->getBaseCreditReturnMax(), $baseAmount);
                $baseAmount = $creditmemo->getBaseCreditAmount() + $baseAmount;
            }
        }

        if ($baseAmount > 0) {
            $baseAmount = $creditmemo->roundPrice($baseAmount);
            $amount = $this->calculationHelper->convertToCurrency(
                $baseAmount,
                $creditmemo->getBaseCurrencyCode(),
                $creditmemo->getOrderCurrencyCode(),
                $creditmemo->getStore()
            );
            $amount = round($amount, 2, PHP_ROUND_HALF_DOWN);

            if (!$creditmemo->getDonotReturnAppliedCredits() && !$creditmemo->getId()) {
                $creditmemo->setCreditTotalRefunded($amount);
                $creditmemo->setBaseCreditTotalRefunded($baseAmount);
            }

            $creditmemo->setCreditRefundFlag(true);
            $creditmemo->setPaymentRefundDisallowed(false);
        }
    }
}
