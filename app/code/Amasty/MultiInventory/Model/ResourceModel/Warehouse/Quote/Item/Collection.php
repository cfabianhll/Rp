<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item as ItemResource;
use Amasty\MultiInventory\Model\Warehouse\Quote\Item;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Item[] getItems()
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
     * @param $quoteId
     * @return $this
     */
    public function getDataQuote($quoteId)
    {
        $this->addFieldToFilter('main_table.quote_id', $quoteId);
        if ($this->getSize()) {
            $this->getSelect()
                ->joinLeft(
                    ['soi' => $this->getTable('quote_item')],
                    'soi.item_id = main_table.quote_item_id',
                    ['product' => 'soi.product_id', 'item' => 'soi.item_id', 'parent' => 'soi.parent_item_id']
                )->joinLeft(
                    ['whp' => $this->getTable('amasty_multiinventory_warehouse_item')],
                    'whp.warehouse_id = main_table.warehouse_id AND whp.product_id = soi.product_id'
                );
        }

        return $this;
    }

    /**
     * @param $quoteId
     * @return $this
     */
    public function getQuoteItemInfo($quoteId)
    {
        $this->addFieldToFilter('main_table.quote_id', $quoteId);
        $this->getSelect()->joinLeft(
            ['soi' => $this->getTable('sales_quote_item')],
            'soi.item_id = main_table.quote_item_id',
            ['parent_item_id', 'product_id', 'qty_Quoteed']
        );

        return $this;
    }

    /**
     * @param $quoteId
     * @return $this
     */
    public function getWarehousesFromQuote($quoteId)
    {
        $this->addFieldToFilter('quote_id', $quoteId);

        return $this;
    }
}
