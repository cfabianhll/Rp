<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Warehouse\Item;

use Magento\Framework\DataObject;

/**
 * Class ValidatorResultData
 */
class ValidatorResultData extends DataObject
{
    /**
     * @param int $id
     *
     * @return $this
     */
    public function setQuoteId($id)
    {
        return $this->setData('quote_id', $id);
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->_getData('quote_id');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setOrderId($id)
    {
        return $this->setData('order_id', $id);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->_getData('order_id');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setQuoteItemId($id)
    {
        return $this->setData('quote_item_id', $id);
    }

    /**
     * @return int
     */
    public function getQuoteItemId()
    {
        return $this->_getData('quote_item_id');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setOrderItemId($id)
    {
        return $this->setData('order_item_id', $id);
    }

    /**
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->_getData('order_item_id');
    }

    /**
     * @param int $qty
     *
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData('qty', $qty);
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->_getData('qty');
    }

    /**
     * @param int $qty
     *
     * @return $this
     */
    public function setBackorderedQty($qty)
    {
        return $this->setData('backordered_qty', $qty);
    }

    /**
     * @return int
     */
    public function getBackorderedQty()
    {
        return $this->_getData('backordered_qty');
    }

    /**
     * @param int $qty
     *
     * @return $this
     */
    public function setWarehouseId($qty)
    {
        return $this->setData('warehouse_id', $qty);
    }

    /**
     * @return int
     */
    public function getWarehouseId()
    {
        return $this->_getData('warehouse_id');
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setProductId($id)
    {
        return $this->setData('product_id', $id);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    /**
     * @return bool
     */
    public function getIsSplitted()
    {
        return $this->_getData('is_splitted');
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function setIsSplitted($flag)
    {
        return $this->setData('is_splitted', $flag);
    }

    /**
     * @return bool
     */
    public function getIsChanged()
    {
        return $this->_getData('is_changed');
    }

    /**
     * @param bool $flag
     *
     * @return $this
     */
    public function setIsChanged($flag)
    {
        return $this->setData('is_changed', $flag);
    }
}
