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



namespace Mirasvit\Credit\Block\Adminhtml\Sales\Order\Creditmemo;

class Controls extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Mirasvit\Credit\Helper\Data
     */
    protected $creditData;

    /**
     * Controls constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Mirasvit\Credit\Helper\Data $creditData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Mirasvit\Credit\Helper\Data $creditData,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->context = $context;
        $this->creditData = $creditData;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function canRefundToCredit()
    {
        if ($this->registry->registry('current_creditmemo')->getOrder()->getCustomerIsGuest()) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    public function getReturnValue()
    {
        $max = round($this->registry->registry('current_creditmemo')->getBaseCreditReturnMax(), 2);

        if ($max) {
            return $max;
        }

        return 0;
    }

    /**
     * @return float
     */
    public function getAppliedValue()
    {
        $params = $this->getRequest()->getParam('creditmemo');
        if (!$this->creditData->isAutoRefundEnabled() && (empty($params) || !isset($params['return_applied_credits']))) {
            return 0;
        }
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $this->registry->registry('current_creditmemo');
        if ($creditmemo->getCreditTotalRefunded()) {
            return $creditmemo->getCreditTotalRefunded();
        }

        $order = $creditmemo->getOrder();
        $credits = round($order->getBaseCreditAmount() - $order->getBaseCreditTotalRefunded(), 2);
        if ($credits) {
            return $credits;
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditMemo */
        $creditMemo = $this->registry->registry('current_creditmemo');
        $baseCode = $creditMemo->getBaseCurrencyCode();
        $code = $creditMemo->getOrderCurrencyCode();

        if ($baseCode && $baseCode != $code) {
            return $baseCode;
        }

        return '';
    }

    /**
     * @return \Mirasvit\Credit\Helper\Data
     */
    public function getCreditData()
    {
        return $this->creditData;
    }

    /**
     * Get update url
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl(
            'sales/*/updateQty',
            [
                'order_id'   => $this->registry->registry('current_creditmemo')->getOrderId(),
                'invoice_id' => $this->getRequest()->getParam('invoice_id', null)
            ]
        );
    }
}
