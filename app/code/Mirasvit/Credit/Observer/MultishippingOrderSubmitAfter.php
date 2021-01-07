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

use Magento\Framework\Event\Observer;
use Mirasvit\Credit\Model\Transaction;

class MultishippingOrderSubmitAfter extends AbstractObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $orders = $observer->getEvent()->getOrders();// uses only for multishipping
        if (!empty($orders)) {
            foreach ($orders as $order) {
                /** @var \Magento\Sales\Model\Order $order */
                if ($order->getBaseCreditAmount() <= 0) {
                    continue;
                }
                $balance = $this->creditHelper->getBalance($order->getCustomerId(), $order->getOrderCurrencyCode());
                $balance->setTransactionCurrencyCode($order->getOrderCurrencyCode());

                $balance->addTransaction(
                    -1 * $order->getCreditAmount(),
                    -1 * $order->getBaseCreditAmount(),
                    Transaction::ACTION_USED,
                    ['order' => $order]
                );
            }
        }
    }
}
