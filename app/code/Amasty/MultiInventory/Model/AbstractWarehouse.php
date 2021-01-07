<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\WarehouseAbstractInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class AbstractWarehouse
 */
class AbstractWarehouse extends AbstractExtensibleModel implements WarehouseAbstractInterface
{
    /**
     * @var Warehouse
     */
    public $warehouse;

    /**
     * @var WarehouseFactory
     */
    public $warehouseFactory;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setWarehouseId($id)
    {
        $this->setData(self::WAREHOUSE_ID, $id);
        return $this;
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return AbstractWarehouse
     */
    public function setWarehouse(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
        $this->setWarehouseId($warehouse->getId());
        return $this;
    }

    /**
     * @return Warehouse|null
     */
    public function getWarehouse()
    {
        if ($this->warehouse === null && ($warehouseId = $this->getWarehouseId())) {
            $warehouse = $this->warehouseFactory->create();
            $warehouse->load($warehouseId);
            $this->setWarehouse($warehouse);
        }

        return $this->warehouse;
    }
}
