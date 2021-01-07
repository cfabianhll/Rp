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



namespace Mirasvit\Rma\Helper\Controller\Rma;

class CustomerStrategy extends AbstractStrategy
{
    /**
     * @var \Magento\Sales\Model\Order[]|null
     */
    private $orders = null;

    /**
     * @var \Mirasvit\Rma\Helper\Rma\Url
     */
    private $rmaUrl;
    /**
     * @var \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface
     */
    private $orderManagement;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Strategy\SearchInterface
     */
    private $strategySearch;
    /**
     * @var \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface
     */
    private $performerFactory;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * CustomerStrategy constructor.
     * @param \Mirasvit\Rma\Helper\Rma\Url $rmaUrl
     * @param \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Service\Strategy\SearchInterface $strategySearch
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performerFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Api\Service\Order\OrderManagementInterface $orderManagement,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Service\Strategy\SearchInterface $strategySearch,
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performerFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->rmaUrl           = $rmaUrl;
        $this->orderManagement  = $orderManagement;
        $this->rmaRepository    = $rmaRepository;
        $this->strategySearch   = $strategySearch;
        $this->performerFactory = $performerFactory;
        $this->orderRepository  = $orderRepository;
        $this->customerSession  = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function initRma(\Magento\Framework\App\RequestInterface $request)
    {
        $id = $request->getParam('id');

        return $this->rmaRepository->getByGuestId($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaId(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $rma->getGuestId();
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaList($order = null)
    {
        return $this->strategySearch->getRmaList(
            $this->getCustomer()->getId(),
            $order
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformer()
    {
        return $this->performerFactory->create(
            \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface::CUSTOMER,
            $this->getCustomer()
        );
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if (!$this->customer) {
            $this->customer = $this->customerSession->getCustomer();
        }

        return $this->customer;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrderList()
    {
        if ($this->orders === null) {
            $this->orders = $this->orderManagement->getAllowedOrderList($this->getCustomer());
        }

        return $this->orders;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaUrl->getGuestUrl($rma);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRmaUrl()
    {
        return $this->rmaUrl->getCreateUrl();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return string
     */
    public function getUrl($rma)
    {
        return $this->rmaUrl->getGuestUrl($rma);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaUrl->getGuestPrintUrl($rma);
    }
}
