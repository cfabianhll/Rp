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



namespace Mirasvit\Credit\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Credit extends AbstractTotal
{
    /**
     * @var \Mirasvit\Credit\Service\Config\CalculationConfig
     */
    private $calculationConfig;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * Credit constructor.
     * @param \Mirasvit\Credit\Service\Config\CalculationConfig $calculationConfig
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Credit\Service\Config\CalculationConfig $calculationConfig,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    ) {
        parent::__construct($data);

        $this->calculationConfig = $calculationConfig;
        $this->request = $request;
    }
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     *
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $input = $this->request->getParam('creditmemo');
        if (isset($input['return_applied_credits']) && $input['return_applied_credits'] <= 0) {
            $creditmemo->setDonotReturnAppliedCredits(1);
        }

        $creditmemo->setBaseCreditTotalRefunded(0)
            ->setCreditTotalRefunded(0)
            ->setBaseCreditReturnMax(0)
            ->setCreditReturnMax(0);

        $order = $creditmemo->getOrder();

        $baseUsed = 0;
        $used = 0;
        // we do not apply discount to shipping, so creditmemo grand total could be equal shipping amount
        if (abs($order->getBaseDiscountInvoiced()) > $order->getBaseSubtotalInvoiced() &&
            ($creditmemo->getBaseGrandTotal() == 0 || $creditmemo->getBaseGrandTotal() == $creditmemo->getBaseShippingAmount()) &&
            $creditmemo->getBaseShippingAmount() <= abs(abs($order->getBaseDiscountInvoiced()) - abs($order->getBaseSubtotalInvoiced()))
        ) {
            $baseUsed = $order->getBaseCreditInvoiced() - $order->getBaseCreditRefunded();
            $used     = $order->getCreditInvoiced() - $order->getCreditRefunded();

            $creditmemo->setBaseCreditAmount($baseUsed);
            $creditmemo->setCreditAmount($used);
            $creditmemo->setBaseGrandTotal(0);
            $creditmemo->setGrandTotal(0);

            $creditmemo->setAllowZeroGrandTotal(true);
        } elseif ($order->getBaseCreditAmount() && $order->getBaseCreditInvoiced()) {
            if ($order->getBaseCreditInvoiced() > 0 && $creditmemo->getBaseGrandTotal() <= 0) {
                if (!$creditmemo->getDonotReturnAppliedCredits()) {
                    $baseUsed = $order->getBaseCreditInvoiced() - $order->getBaseCreditTotalRefunded();
                    $used = $order->getCreditInvoiced() - $order->getCreditTotalRefunded();

                    $creditmemo->setBaseGrandTotal(0);
                    $creditmemo->setGrandTotal(0);

                    $creditmemo->setAllowZeroGrandTotal(true);
                }
            } else {
                if (!$creditmemo->getDonotReturnAppliedCredits()) {
                    $baseUsed = $order->getBaseCreditInvoiced() - $order->getBaseCreditRefunded();
                    $used = $order->getCreditInvoiced() - $order->getCreditRefunded();
                }
            }

            $creditmemo->setBaseCreditAmount($baseUsed);
            $creditmemo->setCreditAmount($used);
            // credits are already subtracted from grand total
            if ($this->calculationConfig->getCreditTotalOrder() > 410) {
                $baseUsed = 0;
                $used = 0;
            }
        }

        $returnMax = $baseReturnMax = 0;
        if ($creditmemo->getBaseGrandTotal() > $baseUsed) {
            $baseReturnMax = $creditmemo->getBaseGrandTotal() - $baseUsed;
        }
        if ($creditmemo->getGrandTotal() > $used) {
            $returnMax = $creditmemo->getGrandTotal() - $used;
        }
        $creditmemo->setBaseCreditReturnMax($baseReturnMax);
        $creditmemo->setCreditReturnMax($returnMax);

        return $this;
    }
}
