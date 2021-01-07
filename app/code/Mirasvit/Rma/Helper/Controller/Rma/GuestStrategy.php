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

use Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GuestStrategy extends AbstractStrategy
{
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Url
     */
    private $rmaUrl;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\Rma\Model\OfflineOrderFactory
     */
    private $offlineOrderFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface
     */
    private $performerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface
     */
    private $offlineOrderRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Strategy\SearchInterface
     */
    private $strategySearch;
    /**
     * @var OfflineOrderConfigInterface
     */
    private $offlineConfig;
    /**
     * @var \Mirasvit\Rma\Api\Config\FrontendConfigInterface
     */
    private $frontendConfig;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrderService;

    /**
     * GuestStrategy constructor.
     * @param \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Config\FrontendConfigInterface $frontendConfig
     * @param OfflineOrderConfigInterface $offlineConfig
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService
     * @param \Mirasvit\Rma\Api\Service\Strategy\SearchInterface $strategySearch
     * @param \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performerFactory
     * @param \Mirasvit\Rma\Helper\Rma\Url $rmaUrl
     * @param \Mirasvit\Rma\Model\OfflineOrderFactory $offlineOrderFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Config\FrontendConfigInterface $frontendConfig,
        \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface $offlineConfig,
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        \Mirasvit\Rma\Api\Service\Strategy\SearchInterface $strategySearch,
        \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface $performerFactory,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Model\OfflineOrderFactory $offlineOrderFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->rmaUrl                 = $rmaUrl;
        $this->offlineOrderRepository = $offlineOrderRepository;
        $this->rmaRepository          = $rmaRepository;
        $this->frontendConfig         = $frontendConfig;
        $this->offlineConfig          = $offlineConfig;
        $this->rmaOrderService        = $rmaOrderService;
        $this->strategySearch         = $strategySearch;
        $this->offlineOrderFactory    = $offlineOrderFactory;
        $this->orderRepository        = $orderRepository;
        $this->customerRepository     = $customerRepository;
        $this->customerSession        = $customerSession;
        $this->performerFactory       = $performerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return false;
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
    public function initRma(\Magento\Framework\App\RequestInterface $request)
    {
        $id = $request->getParam('id');
        $rma = $this->rmaRepository->getByGuestId($id);

        return $rma;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaList($order = null)
    {
        if ($this->customerSession->getRMAGuestOrderId() == OfflineOrderConfigInterface::OFFLINE_ORDER_PLACEHOLDER) {
            return [];
        }
        if ($this->frontendConfig->showGuestRmaByOrder()) {
            $customerId = 0;
            if (!$order) {
                $order = $this->getOrder();
            }
        } else {
            $customerId = $this->getOrder()->getCustomerId();
            if (!$customerId && $this->offlineConfig->isOfflineOrdersEnabled()) {
                if (!$order) {
                    $order = $this->getOrder();
                }
            }
        }

        return $this->strategySearch->getRmaList(
            $customerId,
            $order
        );
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        $orderId = $this->customerSession->getRMAGuestOrderId();
        $isOfflineOrder = $this->customerSession->getRMAGuestOrderIsOffline();
        if ($orderId == OfflineOrderConfigInterface::OFFLINE_ORDER_PLACEHOLDER) {
            $order = $this->offlineOrderFactory->create();
            if (!empty($this->customerSession->getRMAFirstname())) {
                $order->setCustomerFirstname($this->customerSession->getRMAFirstname());
            }
            if (!empty($this->customerSession->getRMALastname())) {
                $order->setCustomerLastname($this->customerSession->getRMALastname());
            }
            if (!empty($this->customerSession->getRMAEmail())) {
                $order->setCustomerEmail($this->customerSession->getRMAEmail());
            }
        } else {
            if ($isOfflineOrder) {
                try {
                    $order = $this->offlineOrderRepository->get($orderId);
                } catch (NoSuchEntityException $e) {
                    $order = $this->orderRepository->get($orderId);
                }
            } else {
                $order = $this->orderRepository->get($orderId);
            }
        }

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformer()
    {
        $order = $this->getOrder();
        $name = implode(
            ' ',
            [$order->getCustomerFirstname(), $order->getCustomerMiddlename(), $order->getCustomerLastname()]
        );

        if (empty(trim($name))) {
            if ($order->getIsOffline()) {
                if ($order->getCustomerId()) {
                    $customer = $this->customerRepository->getById($order->getCustomerId());
                    $name = implode(
                        ' ',
                        [$customer->getFirstname(), $customer->getMiddlename(), $customer->getLastname()]
                    );
                }
            } else {
                $address = $order->getBillingAddress();
                $name = implode(
                    ' ',
                    [$address->getFirstname(), $address->getMiddlename(), $address->getLastname()]
                );
            }
        }

        return $this->performerFactory->create(
            \Mirasvit\Rma\Api\Service\Performer\PerformerFactoryInterface::GUEST,
            new \Magento\Framework\DataObject(
                [
                    'name'  => $name,
                    'email' => $order->getCustomerEmail(),
                    'id'    => $order->getCustomerId(),
                ]
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrderList()
    {
        if ($this->getOrder()->getIsOffline()) {
            $name = $this->customerSession->getRMAFirstname() . ' ' . $this->customerSession->getRMALastname();
            return [
                OfflineOrderConfigInterface::OFFLINE_ORDER_PLACEHOLDER => $this->offlineOrderFactory->create()
                    ->setId(OfflineOrderConfigInterface::OFFLINE_ORDER_PLACEHOLDER)
                    ->setCustomerName($name)
                    ->setCustomerEmail($this->customerSession->getRMAEmail())
            ];
        }

        return [$this->getOrder()->getId() => $this->getOrder()];
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
        $order = $this->getOrder();
        if ($order->getIsOffline()) {
            return '';
        }
        return $this->rmaUrl->getCreateUrl($order);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintUrl(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaUrl->getGuestPrintUrl($rma);
    }
}