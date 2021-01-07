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



namespace Mirasvit\Rma\Block\Rma\View;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Items extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Data\OfflineOrderInterface|\Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface
     */
    private $offlineOrderRepository;
    /**
     * @var \Mirasvit\Rma\Service\Config\RmaRequirementConfig
     */
    private $config;
    /**
     * @var \Mirasvit\Rma\Model\ItemFactory
     */
    private $itemFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;
    /**
     * @var \Mirasvit\Rma\Helper\Item\Html
     */
    private $rmaItemHtml;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface
     */
    private $itemProductManagement;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository
     * @param \Mirasvit\Rma\Service\Config\RmaRequirementConfig $config
     * @param \Mirasvit\Rma\Model\ItemFactory $itemFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Repository\OfflineOrderRepositoryInterface $offlineOrderRepository,
        \Mirasvit\Rma\Service\Config\RmaRequirementConfig $config,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->rmaSearchManagement    = $rmaSearchManagement;
        $this->rmaManagement          = $rmaManagement;
        $this->offlineOrderRepository = $offlineOrderRepository;
        $this->config                 = $config;
        $this->itemFactory            = $itemFactory;
        $this->registry               = $registry;
        $this->imageHelper            = $imageHelper;
        $this->rmaItemHtml            = $rmaItemHtml;
        $this->itemManagement         = $itemManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->productFactory         = $productFactory;
        $this->context                = $context;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order[]|\Mirasvit\Rma\Api\Data\OfflineOrderInterface[]
     */
    public function getOrders()
    {
        return $this->rmaManagement->getOrders($this->getRma());
    }

    /**
     * @param \Magento\Sales\Model\Order|\Mirasvit\Rma\Api\Data\OfflineOrderInterface $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order|\Mirasvit\Rma\Api\Data\OfflineOrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function isReasonAllowed()
    {
        return $this->config->isCustomerReasonRequired();
    }

    /**
     * @return bool
     */
    public function isConditionAllowed()
    {
        return $this->config->isCustomerConditionRequired();
    }

    /**
     * @return bool
     */
    public function isResolutionAllowed()
    {
        return $this->config->isCustomerResolutionRequired();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->registry->registry('current_rma');
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems()
    {
        $items = $this->rmaSearchManagement->getRequestedItems($this->getRma());
        $currentOrder = $this->getOrder();

        $result = [];
        foreach ($items as $item) {
            $itemOrderId = $this->getOrderId($item);
            if ($itemOrderId == $currentOrder->getId()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\OfflineItemInterface[]
     */
    public function getOfflineItems()
    {
        $items = $this->rmaSearchManagement->getRequestedOfflineItems($this->getRma());
        $currentOrder = $this->getOrder();

        $result = [];
        foreach ($items as $item) {
            $itemOrder = $this->getOfflineOrder($item);
            if ($itemOrder->getOfflineOrderId() == $currentOrder->getOfflineOrderId()) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string                               $imageId
     * @param array                                $attributes
     * @return \Magento\Catalog\Helper\Image
     */
    public function initImage($item, $imageId, $attributes = [])
    {
        return $this->itemProductManagement->getImage($item, $imageId, $attributes);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($item)
    {
        return $this->itemProductManagement->getProduct($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemLabel(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemLabel($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemSku(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemSku($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getOrderItemOrderIncrement(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemOrderIncrement($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getItemWeight(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $weight = $this->getProduct($item)->getWeight() * $item->getQtyRequested();

        if (!$weight) {
            $weight = '--';
        }

        return $weight;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return string
     */
    public function getReasonName($item)
    {
        return $this->itemManagement->getReasonName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return string
     */
    public function getConditionName($item)
    {
        return $this->itemManagement->getConditionName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return string
     */
    public function getResolutionName($item)
    {
        return $this->itemManagement->getResolutionName($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface|\Mirasvit\Rma\Api\Data\OfflineItemInterface $item
     * @return \Mirasvit\Rma\Api\Data\OfflineOrderInterface
     */
    public function getOfflineOrder($item)
    {
        return $this->offlineOrderRepository->get($item->getOfflineOrderId());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getOrderId($item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        return $orderItem->getOrderId();
    }
}
