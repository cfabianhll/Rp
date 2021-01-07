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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Edit\Form;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Items extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;

    /**
     * @var \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    private $items;

    /**
     * @var \Mirasvit\Core\Helper\Image
     */
    private $imageHelper;
    /**
     * @var int
     */
    private $increment = 0;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface
     */
    private $itemListBuilder;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface
     */
    private $itemQuantityManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface
     */
    private $itemProductManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Helper\Item\Option
     */
    private $rmaItemOption;
    /**
     * @var \Mirasvit\Rma\Helper\Item\Html
     */
    private $rmaItemHtml;
    /**
     * @var \Mirasvit\Rma\Model\ItemFactory
     */
    private $itemFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @param \Mirasvit\Core\Helper\Image $imageHelper
     * @param \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Helper\Item\Option $rmaItemOption
     * @param \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml
     * @param \Mirasvit\Rma\Model\ItemFactory $itemFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Core\Helper\Image $imageHelper,
        \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface $itemQuantityManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Helper\Item\Option $rmaItemOption,
        \Mirasvit\Rma\Helper\Item\Html $rmaItemHtml,
        \Mirasvit\Rma\Model\ItemFactory $itemFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->imageHelper            = $imageHelper;
        $this->itemListBuilder        = $itemListBuilder;
        $this->itemManagement         = $itemManagement;
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->itemProductManagement  = $itemProductManagement;
        $this->rmaManagement          = $rmaManagement;
        $this->rmaItemOption          = $rmaItemOption;
        $this->rmaItemHtml            = $rmaItemHtml;
        $this->itemFactory            = $itemFactory;
        $this->productFactory         = $productFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\RmaInterface
     */
    public function getRma()
    {
        return $this->getData('rma');
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    public function getOrders()
    {
        return $this->rmaManagement->getOrders($this->getRma());
    }

    /**
     * @param int $i
     *
     * @return Items
     */
    public function setIncrement($i)
    {
        $this->increment = $i;

        return $this;
    }

    /**
     * @return int
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @return int
     */
    public function getRmaStoreId()
    {
        $rma = $this->getRma();

        if (!$rma->getId()) {
            return 0;
        }

        $storeId = $rma->getStoreId();
        if (!$storeId) {
            $customer = $this->rmaManagement->getCustomer($rma);
            $storeId = $customer->getStoreId();
        }

        return $storeId;
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItems()
    {
        $order = $this->getOrder();
        if (!empty($this->items[$order->getId()])) {
            return $this->items[$order->getId()];
        }
        $rma = $this->getRma();
        if ($rma->getId()) {
            $this->items[$order->getId()] = $this->itemListBuilder->getRmaItems($rma, $order->getIsOffline());
        } else {
            $this->items[$order->getId()] = $this->itemListBuilder->getList($order);
        }

        return $this->items[$order->getId()];
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @param string $imageId
     * @param array $attributes
     * @return \Mirasvit\Core\Helper\Image
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Json_Exception
     */
    public function initImage($item, $imageId, $attributes = [])
    {
        $orderItem = $this->itemManagement->getOrderItem($item);
        $item->setProductOptions($orderItem->getProductOptions());
        $item->setStoreId($orderItem->getData('store_id'));
        $options = $item->getProductOptions();
        if (!empty($options['simple_sku'])) {
            $childItem = $this->itemFactory->create()->setSku($options['simple_sku']);
            $childItem->setStoreId($orderItem->getData('store_id'));
            $product   = $this->getProduct($childItem);
            $image     = $this->imageHelper->init($product, $imageId, $attributes);
            if ($image->isImagePlaceholder()) {//if child does not have img, use parent
                $product = $this->getProduct($item);
            }
        } else {
            $product = $this->getProduct($item);
        }
        $image = $this->imageHelper->init($product, $imageId, $attributes);
        if ($image->isImagePlaceholder()) {
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
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $product = $this->itemProductManagement->getProduct($item);
        $item->setProductId($product->getId());

        return $product;
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
    public function getOrderItemPrice(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->rmaItemHtml->getItemPrice($item, $this->getOrder());
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
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getConditionList()
    {
        return $this->rmaItemOption->getConditionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getResolutionList()
    {
        return $this->rmaItemOption->getResolutionList();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReturnInterface[]
     */
    public function getReasonList()
    {
        return $this->rmaItemOption->getReasonList();
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return boolean|int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getIsBundleItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return false;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyStock(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyStock($item->getProductId());
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyOrdered(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyOrdered($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getQtyAvailable(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        return $this->itemQuantityManagement->getQtyAvailable($item);
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\ItemInterface $item
     * @return int
     */
    public function getMaxAllowed(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $avl = $this->getQtyAvailable($item);

        return $this->getRma()->getId() ? $avl + $item->getQtyRequested() : $avl;
    }
}