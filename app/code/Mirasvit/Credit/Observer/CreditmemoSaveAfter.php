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

class CreditmemoSaveAfter implements ObserverInterface
{
    /**
     * @var \Mirasvit\Credit\Helper\CreditOption
     */
    private $optionHelper;
    /**
     * @var \Mirasvit\Credit\Helper\Calculation
     */
    private $culculationHelper;
    /**
     * @var \Mirasvit\Credit\Model\ProductOptionCreditFactory
     */
    private $productOptionCredit;
    /**
     * @var \Mirasvit\Credit\Helper\Data
     */
    private $creditData;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * CreditmemoSaveAfter constructor.
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Mirasvit\Credit\Helper\CreditOption $optionHelper
     * @param \Mirasvit\Credit\Helper\Calculation $culculationHelper
     * @param \Mirasvit\Credit\Model\ProductOptionCreditFactory $productOptionCredit
     * @param \Mirasvit\Credit\Helper\Data $creditData
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Mirasvit\Credit\Helper\CreditOption $optionHelper,
        \Mirasvit\Credit\Helper\Calculation $culculationHelper,
        \Mirasvit\Credit\Model\ProductOptionCreditFactory $productOptionCredit,
        \Mirasvit\Credit\Helper\Data $creditData
    ) {
        $this->optionHelper        = $optionHelper;
        $this->culculationHelper   = $culculationHelper;
        $this->productOptionCredit = $productOptionCredit;
        $this->creditData          = $creditData;
        $this->productMetadata     = $productMetadata;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        $order      = $creditmemo->getOrder();

        $this->refundCreditProduct($creditmemo);

        if ($creditmemo->getAutomaticallyCreated()) {
            if ($this->creditData->isAutoRefundEnabled()) {
                $creditmemo->setCreditRefundFlag(true)
                    ->setCreditTotalRefunded($creditmemo->getCreditAmount())
                    ->setBaseCreditTotalRefunded($creditmemo->getBaseCreditAmount());
            } else {
                return;
            }
        }

        $baseCreditReturnMax = floatval($creditmemo->getBaseCreditReturnMax());

        $refunded = round($creditmemo->getBaseCreditTotalRefunded(), 2);
        $used     = round($baseCreditReturnMax + $order->getBaseCreditAmount(), 2);

        if ($refunded > $used) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Store credit amount cannot exceed order amount.')
            );
        }
        if ($creditmemo->getData('credit_refund_flag')
            && $creditmemo->getData('base_credit_total_refunded')
            && !$creditmemo->getDonotReturnAppliedCredits()
        ) {

            $order->setBaseCreditTotalRefunded(
                $order->getBaseCreditTotalRefunded() + $creditmemo->getBaseCreditTotalRefunded()
            );
            $order->setCreditTotalRefunded(
                $order->getCreditTotalRefunded() + $creditmemo->getCreditTotalRefunded()
            );

            if (version_compare($this->productMetadata->getVersion(), "2.3.0", "<")) {
                $order->save();
            }

            $balance = $this->creditData->getBalance($order->getCustomerId(), $order->getOrderCurrencyCode());
            $balance->setTransactionCurrencyCode($order->getOrderCurrencyCode());
            $balance->addTransaction(
                $creditmemo->getData('credit_total_refunded'),
                $creditmemo->getData('base_credit_total_refunded'),
                \Mirasvit\Credit\Model\Transaction::ACTION_REFUNDED,
                ['order' => $order, 'creditmemo' => $creditmemo]
            );
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return void
     */
    public function refundCreditProduct($creditmemo)
    {
        $order = $creditmemo->getOrder();

        $credits = 0;
        /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
        foreach ($creditmemo->getAllItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $orderItem = $order->getItemById($item->getOrderItemId());
            if ($orderItem->getProductType() == \Mirasvit\Credit\Model\Product\Type::TYPE_CREDITPOINTS
                || $orderItem->getRealProductType() == \Mirasvit\Credit\Model\Product\Type::TYPE_CREDITPOINTS
            ) {
                $options = $orderItem->getProductOptionByCode('info_buyRequest');
                $option  = $this->productOptionCredit->create();
                $value   = !empty($options['creditOption']) ? $options['creditOption'] : 0;
                $data    = !empty($options['creditOptionData']) ? $options['creditOptionData'] : [];
                $option->setData($data);
                $productCredits = $this->optionHelper->getOptionCredits($option, $value);
                $credits        += $productCredits * $item->getQty();
            }
        }
        if ($credits) {
            $balance = $this->creditData->getBalance($order->getCustomerId(), $order->getOrderCurrencyCode());
            $balance->setTransactionCurrencyCode($order->getOrderCurrencyCode());

            $baseCredits = $this->culculationHelper->convertToCurrency(
                $credits, $order->getOrderCurrencyCode(), $balance->getCurrencyCode(), $order->getStore()
            );
            $balance->addTransaction(
                -1 * $credits,
                -1 * $baseCredits,
                \Mirasvit\Credit\Model\Transaction::ACTION_REFUNDED,
                ['order' => $order, 'creditmemo' => $creditmemo]
            );
        }
    }
}
