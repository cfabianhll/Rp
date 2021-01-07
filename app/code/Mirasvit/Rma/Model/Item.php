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



namespace Mirasvit\Rma\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Mirasvit\Rma\Api\Data\ItemInterface;
use Mirasvit\Rma\Helper\Serializer;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @method \Mirasvit\Rma\Model\ResourceModel\Item\Collection|\Mirasvit\Rma\Model\Item[] getCollection()
 * @method \Mirasvit\Rma\Model\Item load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rma\Model\Item setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rma\Model\Item setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rma\Model\ResourceModel\Item getResource()
 */
class Item extends \Magento\Framework\Model\AbstractModel implements
    IdentityInterface, \Mirasvit\Rma\Api\Data\ItemInterface
{
    const CACHE_TAG = 'rma_item';

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_item';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_item';

    /**
     * Item constructor.
     * @param Serializer $serializer
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     */
    public function __construct(
        Serializer $serializer,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaId()
    {
        return $this->getData(self::KEY_RMA_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaId($rmaId)
    {
        return $this->setData(self::KEY_RMA_ID, $rmaId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductSku()
    {
        return $this->getData(self::KEY_PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductSku($sku)
    {
        return $this->setData(self::KEY_PRODUCT_ID, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::KEY_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::KEY_ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonId()
    {
        return $this->getData(self::KEY_REASON_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setReasonId($reasonId)
    {
        return $this->setData(self::KEY_REASON_ID, $reasonId);
    }

    /**
     * {@inheritdoc}
     */
    public function getResolutionId()
    {
        return $this->getData(self::KEY_RESOLUTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setResolutionId($resolutionId)
    {
        return $this->setData(self::KEY_RESOLUTION_ID, $resolutionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionId()
    {
        return $this->getData(self::KEY_CONDITION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setConditionId($conditionId)
    {
        return $this->setData(self::KEY_CONDITION_ID, $conditionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getQtyRequested()
    {
        return $this->getData(self::KEY_QTY_REQUESTED);
    }

    /**
     * {@inheritdoc}
     */
    public function setQtyRequested($qtyRequested)
    {
        return $this->setData(self::KEY_QTY_REQUESTED, $qtyRequested);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::KEY_CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductOptions($options)
    {
        return $this->setData(self::KEY_PRODUCT_OPTIONS, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getToStock()
    {
        return $this->getData(self::KEY_TO_STOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function setToStock($isToStock)
    {
        return $this->setData(self::KEY_TO_STOCK, $isToStock);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItemId()
    {
        return $this->getData(self::KEY_ORDER_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($orderItemId)
    {
        return $this->setData(self::KEY_ORDER_ITEM_ID, $orderItemId);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Item');
    }

    /**
     * {@inheritdoc}
     */
    public function getProductOptions()
    {
        $options = $this->getData('product_options');
        if (is_string($options)) {
            $options = $this->serializer->unserialize($options);
            $this->setData('product_options', $options);
        }

        return $options;
    }

}
