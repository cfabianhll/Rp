<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface;
use Amasty\MultiInventory\Model\AbstractWarehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class CustomerGroup
 */
class CustomerGroup extends AbstractWarehouse implements WarehouseCustomerGroupInterface
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup::class);
    }

    /**
     * CustomerGroup constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param WarehouseFactory $warehouseFactory
     * @param GroupFactory $groupFactory
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
        GroupFactory $groupFactory,
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
        $this->groupFactory = $groupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->groupFactory->create()->load($this->getGroupId())->getCustomerGroupCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId($id)
    {
        $this->setData(self::GROUP_ID, $id);

        return $this;
    }
}
