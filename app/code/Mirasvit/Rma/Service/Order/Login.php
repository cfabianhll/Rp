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



namespace Mirasvit\Rma\Service\Order;

/**
 * Autorization of guest customer
 */
class Login implements \Mirasvit\Rma\Api\Service\Order\LoginInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderManagement
     */
    private $orderManagement;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface
     */
    private $offlineOrderRepository;

    /**
     * Login constructor.
     * @param \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param OrderManagement $orderManagement
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Service\Order\OrderManagement $orderManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->offlineOrderRepository = $offlineOrderRepository;
        $this->rmaRepository = $rmaRepository;
        $this->orderManagement = $orderManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($orderIncrementId, $emailOrLastname)
    {
        if ($orderIncrementId && $emailOrLastname) {
            $orderIncrementId = trim($orderIncrementId);
            $orderIncrementId = str_replace('#', '', $orderIncrementId);

            $searchCriteria = $this->searchCriteriaBuilder->addFilter('receipt_number', $orderIncrementId);
            $items = $this->offlineOrderRepository->getList($searchCriteria->create())->getItems();
            if (!count($items)) {
                $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderIncrementId);
                $items = $this->orderRepository->getList($searchCriteria->create())->getItems();
            }

            if (count($items)) {
                $order = array_pop($items);
                $emailOrLastname = trim(strtolower($emailOrLastname));
                $orderEmail = strtolower($order->getCustomerEmail());
                $orderName = strtolower($order->getCustomerLastname());
                if ($order->getIsOffline()) {
                    try {
                        $customer = $this->orderManagement->getCustomerForOfflineOrder($order);
                        $orderEmail = $customer->getEmail();
                        $orderName = $customer->getLastname();
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('order_id', $order->getId());
                        if (filter_var($emailOrLastname, FILTER_VALIDATE_EMAIL) !== false) {
                            $searchCriteria->addFilter('email', $emailOrLastname);
                        } else {
                            $searchCriteria->addFilter('lastname', $emailOrLastname);
                        }
                        if ($this->rmaRepository->getList($searchCriteria->create())->getTotalCount()) {
                            return $order;
                        }
                    }
                }
                if ($emailOrLastname != $orderEmail && $emailOrLastname != $orderName) {
                    return false;
                }
                return $order;
            }
        }
        return false;
    }
}