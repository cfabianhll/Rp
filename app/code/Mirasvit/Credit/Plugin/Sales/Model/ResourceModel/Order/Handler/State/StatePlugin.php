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



namespace Mirasvit\Credit\Plugin\Sales\Model\ResourceModel\Order\Handler\State;

/**
 * https://github.com/magento/magento2/issues/12910
 */
class StatePlugin
{

    /**
     * @param object                     $subject
     * @param \callable                  $proceed
     * @param \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Handler\State
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheck(
        $subject, $proceed, $order
    ) {
        $isModified = false;
        if (!$order->isCanceled() && !$order->canUnhold() && !$order->canInvoice() && !$order->canShip()) {
            if (0 == $order->getBaseGrandTotal() && $order->getSubtotalInvoiced() <= $order->getCreditInvoiced()) {
                $isModified = true;
                $order->setBaseGrandTotal(0.0001);
            }
        }
        $result = $proceed($order);
        if ($isModified) {
            $order->setBaseGrandTotal(0);
        }

        return $result;
    }
}