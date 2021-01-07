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

class OrderCancelAfter extends \Mirasvit\Credit\Observer\AbstractObserver
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$observer->getEvent()->getItem()) {
            return;
        }

        $order = $observer->getEvent()->getItem()->getOrder();

        if ($order && $order->getBaseCreditAmount() > 0 && $order->getBaseCreditRefunded() == 0) {
            $balance = $this->creditHelper->getBalance($order->getCustomerId(), $order->getOrderCurrencyCode());
            $balance->setTransactionCurrencyCode($order->getOrderCurrencyCode());
            $balance->addTransaction(
                $order->getCreditAmount(),
                $order->getBaseCreditAmount(),
                \Mirasvit\Credit\Model\Transaction::ACTION_REFUNDED,
                ['order' => $order]
            );

            $order->setBaseCreditRefunded($order->getBaseCreditAmount())
                ->setCreditRefunded($order->getCreditAmount())
                ->setBaseCreditTotalRefunded($order->getBaseCreditAmount())
                ->setCreditTotalRefunded($order->getCreditAmount())
                ->save();
        }
    }
}
