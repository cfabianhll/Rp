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



namespace Mirasvit\Credit\Observer\Refill;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Mirasvit\Credit\Model\Transaction;

class OrderSaveBefore extends AbstractObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$this->config->getRefillProduct()) {
            return;
        }

        if ($order->getState() != $order->getOrigData('state')) {
            if ($order->getState() == Order::STATE_COMPLETE) {
                $amount = $this->getRefillAmount($order);
                $baseAmount = $this->getBaseRefillAmount($order);

                if ($amount > 0) {
                    $this->getBalance($order)->addTransaction(
                        $amount,
                        $baseAmount,
                        Transaction::ACTION_REFILL,
                        ['order' => $order]
                    );
                }
            }
        }
    }
}
