<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item as ItemResource;
use Amasty\MultiInventory\Model\Warehouse\Order\Item;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
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
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param WarehouseFactory $factory
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        WarehouseFactory $factory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->factory = $factory;
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function getDataOrder($orderId)
    {
        $this->addFieldToFilter('main_table.order_id', $orderId);
        if ($this->getSize()) {
            $this->getSelect()
                ->joinLeft(
                    ['soi' => $this->getTable('sales_order_item')],
                    'soi.item_id = main_table.order_item_id',
                    ['product' => 'soi.product_id', 'item' => 'soi.item_id', 'parent' => 'soi.parent_item_id']
                )->joinLeft(
                    ['whp' => $this->getTable('amasty_multiinventory_warehouse_item')],
                    'whp.warehouse_id = main_table.warehouse_id AND whp.product_id = soi.product_id',
                    [
                        'product_id',
                        'warehouse_id',
                        'qty',
                        'available_qty',
                        'ship_qty',
                        'room_shelf',
                        'backorders',
                        'stock_status'
                    ]
                )
                ->where('whp.warehouse_id <> ?', $this->factory->create()->getDefaultId());
        }

        return $this;
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function getOrderItemInfo($orderId)
    {
        $this->addFieldToFilter('main_table.order_id', $orderId);
        $this->getSelect()->joinLeft(
            ['soi' => $this->getTable('sales_order_item')],
            'soi.item_id = main_table.order_item_id',
            ['parent_item_id', 'product_id', 'qty_ordered']
        );

        return $this;
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function getWarehousesFromOrder($orderId)
    {
        $this->addFieldToFilter('order_id', $orderId);

        return $this;
    }
}
