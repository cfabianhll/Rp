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



namespace Mirasvit\Credit\Model\Total\Invoice;

class Credit extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        #credit amount AND credit amount not set for invoice
        if ($order->getBaseCreditAmount()
            && floatval($order->getBaseCreditInvoiced()) == 0
        ) {
            $baseUsed = $order->getBaseCreditAmount();
            $used = $order->getCreditAmount();

            $invoice->setBaseCreditAmount($baseUsed)
                ->setCreditAmount($used);
        }

        return $this;
    }
}
