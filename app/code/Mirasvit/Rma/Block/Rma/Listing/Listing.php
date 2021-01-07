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



namespace Mirasvit\Rma\Block\Rma\Listing;

use Magento\Framework\View\Element\Template;
use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 * @method setCurrentOrder(\Magento\Sales\Model\Order $order)
 * @method \Magento\Sales\Model\Order|null getCurrentOrder()
 */
class Listing extends Template
{
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;

    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    protected $strategy;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var Template\Context
     */
    private $context;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Url
     */
    private $rmaUrl;
    /**
     * @var \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface
     */
    private $orderManagement;

    /**
     * Listing constructor.
     * @param \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Helper\Rma\Url $rmaUrl
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Template\Context $context
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->orderManagement     = $orderManagement;
        $this->strategy            = $strategyFactory->create($context->getRequest());
        $this->rmaManagement       = $rmaManagement;
        $this->rmaSearchManagement = $rmaSearchManagement;
        $this->rmaUrl              = $rmaUrl;
        $this->customerFactory     = $customerFactory;
        $this->customerSession     = $customerSession;
        $this->context             = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return  \Mirasvit\Rma\Api\Data\RmaInterface[]
     */
    public function getRmaList()
    {
        $order = $this->getCurrentOrder();
        if (!is_object($order)) {
            $order = null;
        }

        return $this->strategy->getRmaList($order);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface|\Mirasvit\Rma\Model\OfflineOrder $order
     *
     * @return string
     */
    public function getOrderIncrementId($order)
    {
        return $order->getIsOffline() ? $this->escapeHtml($order->getReceiptNumber()) : $order->getIncrementId();

    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    public function getOrders($rma)
    {
        return $this->rmaManagement->getOrders($rma);
    }

    /**
     * @param RmaInterface $rma
     *
     * @return \Mirasvit\Rma\Api\Data\StatusInterface
     */
    public function getStatus(RmaInterface $rma)
    {
        return $this->rmaManagement->getStatus($rma);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function getCustomer()
    {
        return $this->customerFactory->create()->load($this->customerSession->getCustomerId());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return string
     */
    public function getRmaUrl($rma)
    {
        return $this->strategy->getRmaUrl($rma);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     *
     * @return bool
     */
    public function isLastMessageUnread($rma)
    {
        $messages = $this->rmaSearchManagement->getCustomerUnread($rma);

        return count($messages);
    }

    /**
     * @param RmaInterface $rma
     *
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems(RmaInterface $rma)
    {
        $items = $this->rmaSearchManagement->getRequestedItems($rma);

        return $items;
    }

    /**
     * @return \Mirasvit\Rma\Block\Rma\View\Items
     */
    public function getItemsBlock()
    {
        return $this->_layout->createBlock(\Mirasvit\Rma\Block\Rma\View\Items::class);
    }
}
