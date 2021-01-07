<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item as ItemResource;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Item getFirstItem()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'item_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Item::class, ItemResource::class);
    }

    /**
     * Load Stock item By Product ID and Warehouse ID
     *
     * @param int $productId
     * @param int $warehouseId
     *
     * @return Item
     */
    public function loadProductWarehouse($productId, $warehouseId)
    {
        return $this->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('warehouse_id', $warehouseId)
            ->getFirstItem();
    }

    /**
     * Exclude Total Stock warehouse and disabled warehouses
     *
     * @return $this
     */
    public function addActiveWarehouseFilter()
    {
        return $this->join(['wh' => 'amasty_multiinventory_warehouse'], 'wh.warehouse_id = main_table.warehouse_id', [])
            ->addFieldToFilter('wh.stock_id', ['null' => true]) // only default warehaouse have stock_id
            ->addFieldToFilter('wh.manage', 1);
    }
}
