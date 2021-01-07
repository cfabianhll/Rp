<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseShippingInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Shipping
 */
class Shipping extends AbstractWarehouse implements WarehouseShippingInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping::class);
    }

    /**
     * Shipping constructor.
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
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function getRate()
    {
        return $this->getData(self::RATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingMethod($method)
    {
        $this->setData(self::SHIPPING_METHOD, $method);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRate($rate)
    {
        $this->setData(self::RATE, $rate);

        return $this;
    }
}
