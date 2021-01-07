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



namespace Mirasvit\Credit\Observer\Earning;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

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

        if ($order->getState() != $order->getOrigData('state')) {
            if ($order->getState() == Order::STATE_COMPLETE && !$order->getCustomerIsGuest()) {
                $this->processOrder($order);
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function processOrder($order)
    {
        $rules = $this->earningFactory->create()->getCollection()
            ->addFieldToFilter('is_active', 1);

        $total = 0;

        /** @var \Mirasvit\Credit\Model\Earning $rule */
        foreach ($rules as $rule) {
            $rule = $rule->load($rule->getId());

            $storeId = $order->getStoreId();
            $groupId = $order->getCustomerGroupId();

            if (in_array($storeId, $rule->getStoreIds()) || in_array(0, $rule->getStoreIds())) {
                if (in_array($groupId, $rule->getGroupIds()) || in_array(0, $rule->getGroupIds())) {
                    /** @var \Magento\Sales\Model\Order\Item $item */
                    foreach ($order->getItemsCollection() as $item) {
                        // skip parent items
                        if (!($item->getParentItemId() && $item->getProductType() == 'simple')) {
                            continue;
                        }
                        $isValid = false;
                        // first check item itself, on failure its parent
                        if ($rule->validate($item)) {
                            $isValid = true;
                        } elseif ($item->getParentItemId() && $rule->validate($item->getParentItem())) {
                            $isValid = true;
                        }
                        if ($isValid) {
                            $total += $rule->getEarningItemAmount($item);
                        }
                    }
                }
            }
        }

        if ($total > 0) {
            $balance = $this->getBalance($order);
            $balance->setTransactionCurrencyCode($order->getOrderCurrencyCode());

            $baseTotal = $this->calculationHelper->convertToCurrency(
                $total, $order->getOrderCurrencyCode(), $balance->getCurrencyCode(), $order->getStore()
            );

            $balance->addTransaction(
                $total,
                $baseTotal,
                \Mirasvit\Credit\Model\Transaction::ACTION_EARNING,
                ['order' => $order],
                true
            );
        }
    }
}
