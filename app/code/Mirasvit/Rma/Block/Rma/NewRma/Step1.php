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
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Block\Rma\NewRma;

class Step1 extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface
     */
    private $policyConfig;
    /**
     * @var \Mirasvit\Rma\Helper\Order\Html
     */
    private $rmaOrderHtml;
    /**
     * @var \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface
     */
    private $offlineOrderConfig;
    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    private $strategy;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface
     */
    private $orderManagement;

    /**
     * Step1 constructor.
     * @param \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface $offlineOrderConfig
     * @param \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $policyConfig
     * @param \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface $offlineOrderConfig,
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $policyConfig,
        \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Helper\Order\Html $rmaOrderHtml,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->strategy           = $strategyFactory->create();
        $this->orderManagement    = $orderManagement;
        $this->offlineOrderConfig = $offlineOrderConfig;
        $this->policyConfig       = $policyConfig;
        $this->customerSession    = $customerSession;
        $this->rmaOrderHtml       = $rmaOrderHtml;
        $this->context            = $context;

        parent::__construct($context, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Create RMA'));
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Request New Return'));
        }
    }

    /**
     * @return string
     */
    public function getStep1PostUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('returns/rma/new');
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    public function getAllowedOrderList()
    {
        $orders = $this->strategy->getAllowedOrderList();
        unset($orders['offline']);

        return $orders;
    }

    /**
     * @return bool
     */
    public function getIsAllowedOfflineOrder()
    {
        return $this->offlineOrderConfig->isOfflineOrdersEnabled();
    }

    /**
     * @param int|\Magento\Sales\Api\Data\OrderInterface  $orderId
     * @param bool $orderUrl
     * @return string
     */
    public function getOrderLabel($orderId, $orderUrl = false)
    {
        return $this->rmaOrderHtml->getOrderLabel($orderId, $orderUrl);
    }

    /**
     * @return int
     */
    public function getReturnPeriod()
    {
        return $this->policyConfig->getReturnPeriod();
    }

}