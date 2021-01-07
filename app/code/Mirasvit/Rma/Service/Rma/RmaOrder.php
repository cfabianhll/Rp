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


namespace Mirasvit\Rma\Service\Rma;

use Magento\Framework\Exception\NoSuchEntityException;
use Mirasvit\Rma\Api\Data\RmaInterface;

class RmaOrder implements \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface[]
     */
    private $orders = [];

    /**
     * @var \Mirasvit\Rma\Api\Config\RmaConfigInterface
     */
    private $rmaConfig;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $itemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface
     */
    private $offlineItemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface
     */
    private $offlineOrderRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository
     * @param \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface $offlineItemRepository
     * @param \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $rmaConfig,
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface $offlineItemRepository,
        \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->rmaConfig              = $rmaConfig;
        $this->itemRepository         = $itemRepository;
        $this->offlineItemRepository  = $offlineItemRepository;
        $this->offlineOrderRepository = $offlineOrderRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->orderItemRepository    = $orderItemRepository;
        $this->orderRepository        = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(RmaInterface $rma)
    {
        try {
            $results = $this->getOfflineRmaItemCollection($rma);
            if ($results->getTotalCount()) {
                return $this->offlineOrderRepository->get($rma->getOrderId());
            }
            $results = $this->getRmaItemCollection($rma);
            if ($results->getTotalCount()) {
                $item = current($results->getItems());
                $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
                return $this->orderRepository->get($orderItem->getOrderId());
            }
        } catch (NoSuchEntityException $e) {}
        if ($rma->getOrderId()) {
            try {
                return $this->orderRepository->get($rma->getOrderId());
            } catch (NoSuchEntityException $e) {}
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders(RmaInterface $rma)
    {
        if ($rma->getId() && isset($this->orders[$rma->getId()])) {
            return $this->orders[$rma->getId()];
        }
        try {
            $rmaId = $rma->getId();
            $results = $this->getOfflineRmaItemCollection($rma);
            if ($results->getTotalCount()) {
                foreach ($results->getItems() as $item) {
                    $orderId = $item->getOfflineOrderId();
                    if (!isset($this->orders[$rma->getId()]['offline'.$orderId])) {
                        $this->orders[$rmaId]['offline'.$orderId] = $this->offlineOrderRepository->get($orderId);
                    }
                }
            }
            $results = $this->getRmaItemCollection($rma);
            if ($results->getTotalCount()) {
                foreach ($results->getItems() as $item) {
                    $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
                    $orderId   = $orderItem->getOrderId();
                    if (!isset($this->orders[$rmaId][$orderId])) {
                        $this->orders[$rmaId][$orderItem->getOrderId()] = $this->orderRepository->get($orderId);
                    }
                }
            }
            if (isset($this->orders[$rmaId])) {
                return $this->orders[$rmaId];
            }
        } catch (NoSuchEntityException $e) {}
        // create RMA
        if ($rma->getOrderIds()) {
            try {
                foreach ($rma->getOrderIds() as $orderId) {
                    $this->orders[$rma->getId()][$orderId] = $this->orderRepository->get($orderId);
                }
                return $this->orders[$rma->getId()];
            } catch (NoSuchEntityException $e) {}
        }
        if ($rma->getOrderId()) {
            try {
                return [
                    $rma->getOrderId() => $this->orderRepository->get($rma->getOrderId())
                ];
            } catch (NoSuchEntityException $e) {}
        }

        return [];
    }

    /**
     * @param RmaInterface $rma
     * @return \Magento\Framework\Api\SearchResults
     */
    private function getOfflineRmaItemCollection(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
        ;
        return $this->offlineItemRepository->getList($searchCriteria->create());
    }

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\ItemSearchResultsInterface
     */
    private function getRmaItemCollection(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
        ;
        $items = $this->itemRepository->getList($searchCriteria->create());
//        if (!$items->getTotalCount() && $rma->getOrderId()) {
//            $searchCriteria = $this->searchCriteriaBuilder
//                ->addFilter('order_id', $rma->getOrderId())
//            ;
//            $items = $this->orderItemRepository->getList($searchCriteria->create());
//        }
        return $items;
    }
}

