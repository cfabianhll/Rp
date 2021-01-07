<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Bundle\Model\Product;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Type
 */
class Type
{
    /**
     * @var Warehouse
     */
    private $warehouse;

    /**
     * @var System
     */
    private $system;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Warehouse $warehouse
     * @param System $system
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Warehouse $warehouse,
        System $system,
        StoreManagerInterface $storeManager
    ) {
        $this->warehouse = $warehouse;
        $this->system = $system;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Bundle\Model\Product\Type $subject
     * @param Collection $result
     *
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function afterGetSelectionsCollection(\Magento\Bundle\Model\Product\Type $subject, Collection $result)
    {
        if ($this->system->isMultiEnabled() && $this->system->isLockOnStore()) {
            $warehouses = $this->warehouse->getWarehousesByStoreId($this->storeManager->getStore()->getId());

            if ($warehouses) {
                $warehouseId = $warehouses[0];
                $result->getSelect()->join(
                    ['stockitem' => $result->getTable(Warehouse::AMASTY_INVENTORY_ITEM)],
                    "selection.product_id = stockitem.product_id",
                    []
                )->where(
                    "stockitem.warehouse_id = (?)",
                    $warehouseId
                )->where(
                    "stockitem.stock_status = 1"
                );
            }
        }

        return $result;
    }
}
