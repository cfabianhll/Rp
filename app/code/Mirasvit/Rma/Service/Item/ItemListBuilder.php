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


namespace Mirasvit\Rma\Service\Item;

use Mirasvit\Rma\Api\Data\OfflineItemInterface;

class ItemListBuilder implements  \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $itemRepositry;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface
     */
    private $offlineItemRepositry;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
     */
    private $quantitySearchManagement;
    /**
     * @var \Mirasvit\Rma\Model\ItemFactory
     */
    private $itemFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    private $orderItemFactory;

    /**
     * ItemListBuilder constructor.
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepositry
     * @param \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface $offlineItemRepositry
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $quantitySearchManagement
     * @param \Mirasvit\Rma\Model\ItemFactory $itemFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepositry,
        \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface $offlineItemRepositry,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $quantitySearchManagement,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
    ) {
        $this->itemRepositry            = $itemRepositry;
        $this->offlineItemRepositry     = $offlineItemRepositry;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->quantitySearchManagement = $quantitySearchManagement;
        $this->itemFactory              = $itemFactory;
        $this->productRepository        = $productRepository;
        $this->orderItemFactory         = $orderItemFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaItems($rma, $isOffline = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->create();

        if (!$isOffline) {
            $items = $this->itemRepositry->getList($searchCriteria)->getItems();
            if ($isOffline  === null && !count($items)) {
                $items = $this->offlineItemRepositry->getList($searchCriteria)->getItems();
            }
        } else {
            $items = $this->offlineItemRepositry->getList($searchCriteria)->getItems();
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($order)
    {
        if ($order->getIsOffline()) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(OfflineItemInterface::KEY_OFFLINE_ORDER_ID, $order->getId())
                ->create();

            return $this->offlineItemRepositry->getList($searchCriteria)->getItems();
        }

        $collection = $order->getItemsCollection();
        $items = [];
        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($collection as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($item->getProductType() == 'bundle') {
                $items[$item->getId()] = $this->buildFromOrderItem($item);
                foreach ($item->getChildrenItems() as $bundleItem) {
                    $rmaItem = $this->buildFromOrderItem($bundleItem);
                    $rmaItem->setIsBundleItem(true); //@todo fix here
                    $items[$bundleItem->getId()] = $rmaItem;
                }
            } else {
                $items[$item->getId()] = $this->buildFromOrderItem($item);
            }
        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return \Mirasvit\Rma\Api\Data\ItemInterface
     */
    private function buildFromOrderItem($orderItem)
    {
        /** @var \Mirasvit\Rma\Api\Data\ItemInterface $rmaItem */
        $rmaItem = $this->itemFactory->create();
        $rmaItem->setOrderItemId($orderItem->getId());
        $rmaItem->setProductSku($orderItem->getProductSku() ?: $orderItem->getSku());
        $rmaItem->setName($orderItem->getName());
        $rmaItem->setProductType($orderItem->getProductType());
        $rmaItem->setProductOptions($orderItem->getProductOptions());
        $qtyShipped = $orderItem->getQtyShipped();

        $status = '0';
        if ($product = $orderItem->getProduct()) {
            $status = $product->getRmaStatus();
        } elseif ($orderItem->getSku()) {
            try {
                $product = $this->productRepository->get($orderItem->getSku());
                if ($product) {
                    $status = $product->getRmaStatus();
                    $rmaItem->setProductId($product->getId());
                } else {
                    $status = 1;// is allow by default?
                }
            } catch (\Exception $e) {
                $status = 1;// is allow by default?
            }
        }

        $rmaItem->setIsRmaAllowed((string) $status !== '0');

        // we have option to allow rma when status is processing (for example). so products are not shipped yet.
        if ($qtyShipped == 0) {
            $qtyShipped = $orderItem->getQtyOrdered();
        }
        $qty = $qtyShipped - $this->quantitySearchManagement->getQtyInRma($rmaItem);
        if ($qty < 0) {
            $qty = 0;
        }
        $rmaItem->setQtyAvailable($qty);

        return $rmaItem;
    }
}