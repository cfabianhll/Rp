<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse\Order;

use Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Item
 */
class Item extends AbstractWarehouse implements WarehouseOrderItemInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item::class);
    }

    /**
     * Item constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param WarehouseFactory $warehouseFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        WarehouseFactory $warehouseFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->warehouseFactory = $warehouseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItemId()
    {
        return $this->getData(self::ORDER_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ITEM_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($id)
    {
        $this->setData(self::ORDER_ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($id)
    {
        $this->setData(self::ORDER_ITEM_ID, $id);

        return $this;
    }
}
