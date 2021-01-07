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



namespace Mirasvit\Rma\Block\Rma\NewRma\Step2\Items;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Item extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mirasvit\Rma\Api\Data\ItemInterface
     */
    protected $item;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
     */
    private $itemQuantityManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Item\Html
     */
    private $rmaItemHtml;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Mirasvit\Rma\Model\ItemFactory
     */
    private $itemFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Controller\Rma\AbstractStrategy
     */
    private $strategy;
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $rmaFactory;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface
     */
    private $itemProductManagement;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private $context;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml
     * @param \Mirasvit\Rma\Model\ItemFactory $itemFactory
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->strategy               = $strategyFactory->create();
        $this->rmaItemHtml            = $rmaItemHtml;
        $this->itemFactory            = $itemFactory;
        $this->rmaFactory             = $rmaFactory;
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->itemManagement         = $itemManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->rmaSearchManagement    = $rmaSearchManagement;
        $this->imageHelper            = $imageHelper;
        $this->productFactory         = $productFactory;
        $this->context                = $context;

        parent::__construct($context, $data);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return $this
     */
    public function setItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface  $item
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProductByItem($item)
    {
        return $this->itemProductManagement->getProduct($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return string
     */
    public function getRmasByItem($item)
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $result = [];
        foreach ($this->getRmaItemsByOrderItem($orderItem) as $item) {
            $rma = $this->rmaFactory->create()->load($item->getRmaId());
            $result[] = "<a href='{$this->strategy->getRmaUrl($rma)}' target='_blank'>#{$rma->getIncrementId()}</a>";
        }

        return implode(', ', $result);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItemsByOrderItem($orderItem)
    {
        return $this->rmaSearchManagement->getRmaItemsByOrderItem($orderItem->getItemId());
    }

    /**
     * Initialize Helper to work with Image
     *
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Helper\Image
     */
    public function initImage($item, $imageId, $attributes = [])
    {
        $options = $item->getProductOptions();
        if (!empty($options['simple_sku'])) {
            $childItem = $this->itemFactory->create()->setSku($options['simple_sku']);
            $product   = $this->getProductByItem($childItem);
            $image     = $this->imageHelper->init($product, $imageId, $attributes);
            if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {//if child does not have img, use parent
                $product = $this->getProductByItem($item);
            }
        } else {
            $product = $this->getProductByItem($item);
        }
        $image = $this->imageHelper->init($product, $imageId, $attributes);
        if ($image->getUrl() == $image->getDefaultPlaceholderUrl()) {
            $product = $this->productFactory->create();
            if (!empty($options['super_product_config'])) {//configurable product
                $product->getResource()->load($product, $options['super_product_config']['product_id']);
            } elseif (!empty($options['info_buyRequest']) && isset($options['info_buyRequest']['product'])) {//others
                $product->getResource()->load($product, $options['info_buyRequest']['product']);
            }
        }

        return $this->imageHelper->init($product, $imageId, $attributes);
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
     * @return int
     */
    public function getQtyAvailable(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyAvailable($item);
    }
}