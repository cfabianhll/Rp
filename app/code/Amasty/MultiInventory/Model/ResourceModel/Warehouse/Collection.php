<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse as WarehouseResource;
use Amasty\MultiInventory\Model\Warehouse;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Warehouse[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'warehouse_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Warehouse::class, WarehouseResource::class);
    }

    /**
     * @param $id
     * @return $this
     */
    public function addProduct($id)
    {
        if (!$id) {
            $id = 0;
        }
        $this->getSelect()->joinLeft(
            ['w' => $this->_resource->getTable('amasty_multiinventory_warehouse_item')],
            sprintf('w.warehouse_id = main_table.warehouse_id AND w.product_id=%s', $id),
            [
                'qty' => new \Zend_Db_Expr(sprintf('IFNULL(%s, 0)', 'w.qty')),
                'ship_qty' => new \Zend_Db_Expr(sprintf('IFNULL(%s, 0)', 'w.ship_qty')),
                'available_qty' => new \Zend_Db_Expr(sprintf('IFNULL(%s, 0)', 'w.available_qty')),
                'room_shelf',
                'backorders',
                'stock_status'
            ]
        );

        return $this;
    }
}
