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



namespace Mirasvit\Rma\Block\Rma;

use Magento\Customer\Model\Session;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Template;
use Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface;
use Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface;
use Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory;
use Mirasvit\Rma\Helper\Order\Html as OrderHelper;

class NewRma extends Template
{
    /**
     * @var Template\Context
     */
    private $context;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var JsonHelper
     */
    private $jsonEncoder;

    /**
     * @var OfflineOrderConfigInterface
     */
    private $offlineOrderConfig;

    /**
     * @var RmaPolicyConfigInterface
     */
    private $rmaPolicyConfig;

    /**
     * @var OrderHelper
     */
    private $rmaOrderHtml;

    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    private $strategy;

    /**
     * NewRma constructor.
     * @param OrderHelper $rmaOrderHtml
     * @param OfflineOrderConfigInterface $offlineOrderConfig
     * @param RmaPolicyConfigInterface $rmaPolicyConfig
     * @param StrategyFactory $strategyFactory
     * @param Session $customerSession
     * @param JsonHelper $jsonEncoder
     * @param Template\Context $context
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        OrderHelper $rmaOrderHtml,
        OfflineOrderConfigInterface $offlineOrderConfig,
        RmaPolicyConfigInterface $rmaPolicyConfig,
        StrategyFactory $strategyFactory,
        Session $customerSession,
        JsonHelper $jsonEncoder,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->rmaOrderHtml       = $rmaOrderHtml;
        $this->offlineOrderConfig = $offlineOrderConfig;
        $this->rmaPolicyConfig    = $rmaPolicyConfig;
        $this->customerSession    = $customerSession;
        $this->jsonEncoder        = $jsonEncoder;
        $this->context            = $context;
        $this->strategy           = $strategyFactory->create();
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return string
     */
    public function getPostUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('returns/rma/save', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getOrderTemplateUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('returns/rma/orderTemplate', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getOfflineOrderTemplateUrl()
    {
        return $this->context->getUrlBuilder()->getUrl('returns/rma/offlineOrderTemplate', ['_secure' => true]);
    }

    /**
     * @param int|\Magento\Sales\Api\Data\OrderInterface $order
     * @param bool                                       $orderUrl
     *
     * @return string
     */
    public function getOrderLabel($order, $orderUrl = false)
    {
        return $this->rmaOrderHtml->getOrderLabel($order, $orderUrl);
    }

    /**
     * @return bool
     */
    public function getIsAllowedOfflineOrder()
    {
        return $this->offlineOrderConfig->isOfflineOrdersEnabled();
    }

    /**
     * @return bool
     */
    public function getIsAllowedMulitpleOrders()
    {
        return $this->rmaPolicyConfig->isAllowMultipleOrders();
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
     * @param \Magento\Sales\Api\Data\OrderInterface[] $orders
     *
     * @return array
     */
    public function ordersToOptions($orders)
    {
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'id'    => $order->getId(),
                'label' => $this->getOrderLabel($order),
            ];
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getReturnPeriod()
    {
        return $this->rmaPolicyConfig->getReturnPeriod();
    }

    /**
     * @return array
     */
    public function getMageInit()
    {
        return [
            '*' => [
                'Magento_Ui/js/core/app' => [
                    'components' => [
                        'rmaCreate' => [
                            'component' => 'Mirasvit_Rma/js/create-rma',
                            'config'    => [
                                "OrderTemplateUrl"        => $this->getOrderTemplateUrl(),
                                "OfflineOrderTemplateUrl" => $this->getOfflineOrderTemplateUrl(),
                                "isAllowedStoreOrder"     => (int)($this->customerSession->getRMAGuestOrderId() != 'offline'),
                                "isAllowedOfflineOrder"   => $this->getIsAllowedOfflineOrder(),
                                "isAllowedMulitpleOrders" => $this->getIsAllowedMulitpleOrders(),
                                'allowedOrder'            => $this->ordersToOptions($this->getAllowedOrderList()),

                                'htmlCustomFields' => $this->getChildHtml('rma.new.step2.custom_fields'),
                                'htmlAdditional'   => $this->getChildHtml('rma.new.step2.additional'),
                                'htmlPolicy'       => $this->getChildHtml('rma.new.step2.policy'),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
