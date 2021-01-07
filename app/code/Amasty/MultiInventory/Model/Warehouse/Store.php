<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseStoreInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreFactory as MagentoStoreFactory;

/**
 * Class Store
 */
class Store extends AbstractWarehouse implements WarehouseStoreInterface
{
    /**
     * @var MagentoStoreFactory
     */
    private $storeFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store::class);
    }

    /**
     * Store constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param WarehouseFactory $warehouseFactory
     * @param MagentoStoreFactory $storeFactory
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
        MagentoStoreFactory $storeFactory,
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
        $this->storeFactory = $storeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->storeFactory->create()->load($this->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($id)
    {
        $this->setData(self::STORE_ID, $id);
    }
}
