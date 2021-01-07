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


namespace Mirasvit\Rma\Service\Item\ItemManagement;

use \Magento\Framework\Exception\NoSuchEntityException;

/**
 *  We put here only methods directly connected with Item properties
 */
class Quantity implements \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface
     */
    private $rmaPolicyConfig;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $itemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $stockState;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Quantity constructor.
     * @param \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $rmaPolicyConfig
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface $rmaPolicyConfig,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->rmaPolicyConfig        = $rmaPolicyConfig;
        $this->rmaRepository          = $rmaRepository;
        $this->itemRepository         = $itemRepository;
        $this->itemManagement         = $itemManagement;
        $this->rmaManagement          = $rmaManagement;
        $this->rmaSearchManagement    = $rmaSearchManagement;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->productRepository      = $productRepository;
        $this->stockState             = $stockState;
        $this->orderRepository        = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyStock($productId)
    {
        return $this->stockState->getStockQty($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyOrdered(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return (int) $this->itemManagement->getOrderItem($item)->getQtyOrdered();
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyAvailable(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if ($this->rmaPolicyConfig->isAllowRmaRequestOnlyShipped()) {
            $orderItem = $this->itemManagement->getOrderItem($item);
            $qty = $orderItem->getQtyShipped() > $item->getQtyAvailable() ?
                $item->getQtyAvailable() :
                $orderItem->getQtyShipped();
        } else {
            $qty = $this->getQtyOrdered($item) - $this->getItemQtyReturned($item);
        }

        return $qty > 0 ? (int)$qty : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemQtyReturned(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);

        $order = $this->orderRepository->get($orderItem->getOrderId());

        $rmas = $this->rmaManagement->getRmasByOrder($order);

        $qtyReturned = 0;
        foreach ($rmas as $rma) {
            foreach ($this->rmaSearchManagement->getItems($rma) as $rmaItem) {
                $rmaOrderItem = $this->itemManagement->getOrderItem($rmaItem);
                $rmaOrder = $this->orderRepository->get($rmaOrderItem->getOrderId());
                if ($order->getId() != $rmaOrder->getID()) {
                    continue;
                }
                $options   = $item->getProductOptions();
                $productId = $item->getProductId();//compatibility with versions before 2.0.9
                if (!empty($options['simple_sku'])) {
                    try {
                        $productId = $this->productRepository->get($options['simple_sku'])->getId();
                    } catch (NoSuchEntityException $e) {
                        $productId = 0;
                    }
                }
                if ($productId && $rmaItem->getProductId() == $productId) {
                    $qtyReturned += $rmaItem->getQtyRequested();
                } else {
                    $productSku = $item->getProductSku();
                    if ($rmaItem->getProductSku() != $productSku && !empty($options['simple_sku'])) {
                        $productSku = $options['simple_sku'];
                    }
                    if ($rmaItem->getProductSku() == $productSku) {
                        $qtyReturned += $rmaItem->getQtyRequested();
                    }
                }
            }
        }

        return $qtyReturned;
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyInRma($orderItem)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_item_id', $this->itemManagement->getOrderItem($orderItem)->getId())
        ;

        $items = $this->itemRepository->getList($searchCriteria->create())->getItems();
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item->getQtyRequested();
        }

        return $sum;
    }
}
