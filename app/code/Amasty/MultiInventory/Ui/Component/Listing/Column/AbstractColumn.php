<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class AbstractColumn
 */
class AbstractColumn extends Column
{
    const AMASTY_INVENTORY_ITEM = 'amasty_multiinventory_warehouse_item';

    const AMASTY_INVENTORY = 'amasty_multiinventory_warehouse';

    const AMASTY_INVENTORY_ORDER = 'amasty_multiinventory_warehouse_order_item';

    const CATALOG_INVENTORY = 'cataloginventory_stock_item';

    /**
     * @var WarehouseFactory
     */
    public $factory;

    /**
     * @var WarehouseRepositoryInterface
     */
    public $repository;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var EncoderInterface
     */
    public $jsonEncoder;

    /**
     * @var System
     */
    public $helper;

    /**
     * @var Item
     */
    private $warehouseStockResource;

    /**
     * AbstractColumn constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ResourceConnection $resource
     * @param WarehouseFactory $factory
     * @param WarehouseRepositoryInterface $repository
     * @param EncoderInterface $jsonEncoder
     * @param System $helper
     * @param Item $warehouseStockResource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResourceConnection $resource,
        WarehouseFactory $factory,
        WarehouseRepositoryInterface $repository,
        EncoderInterface $jsonEncoder,
        System $helper,
        Item $warehouseStockResource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->factory = $factory;
        $this->repository = $repository;
        $this->resource = $resource;
        $this->jsonEncoder = $jsonEncoder;
        $this->helper = $helper;
        $this->warehouseStockResource = $warehouseStockResource;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * @return AdapterInterface
     */
    public function getConnection()
    {
        return $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * @param int $id
     * @param string $field
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTotalQty($id, $field = 'qty')
    {
        return $this->warehouseStockResource->getTotalQty($id, $field);
    }

    /**
     * @param $id
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTotalSku($id)
    {
        return $this->warehouseStockResource->getTotalSku($id);
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getWarehouses($productId)
    {
        $resource = $this->factory->create()->getResource();
        $select = $this->getConnection()->select()->from(
            ['wi' => $resource->getTable(self::AMASTY_INVENTORY_ITEM)],
            ['warehouse_id', 'qty']
        );
        $select->joinLeft(
            ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
            'w.warehouse_id = wi.warehouse_id',
            ['title']
        );
        $select->where(
            'wi.product_id = :product_id and wi.warehouse_id <> :warehouse_id and wi.qty > 0'
        );

        $bind = [
            'warehouse_id' => (int)$this->factory->create()->getDefaultId(),
            'product_id' => (int)$productId
        ];

        return $this->getConnection()->fetchAssoc($select, $bind);
    }

    /**
     * @param $productId
     *
     * @return array
     */
    public function getInventory($productId)
    {
        $resource = $this->factory->create()->getResource();
        $select = $this->getConnection()->select()->from(
            ['wi' => $resource->getTable(self::CATALOG_INVENTORY)],
            ['qty']
        );
        $select->joinLeft(
            ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
            'w.stock_id = wi.stock_id',
            ['title']
        );
        $select->where(
            'wi.product_id = :product_id'
        );

        $bind = [
            'product_id' => (int)$productId
        ];

        return $this->getConnection()->fetchAssoc($select, $bind);
    }

    /**
     * @param int $productId
     * @param int|null $warehouseId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductStockData($productId, $warehouseId = null)
    {
        return $this->warehouseStockResource->getProductStockData($productId, $warehouseId);
    }

    /**
     * @param $orderId
     *
     * @return array
     */
    public function getWarehousesOrder($orderId)
    {
        $resource = $this->factory->create()->getResource();
        $select = $this->getConnection()->select()->from(
            ['wi' => $resource->getTable(self::AMASTY_INVENTORY_ORDER)],
            []
        );
        $select->joinLeft(
            ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
            'w.warehouse_id = wi.warehouse_id',
            ['title']
        );
        $select->where(
            'wi.order_id = :order_id'
        );

        $bind = [
            'order_id' => (int)$orderId
        ];
        $result = $this->getConnection()->fetchCol($select, $bind);

        if (!count($result)) {
            $select = $this->getConnection()->select()->from(
                ['w' => $resource->getTable(self::AMASTY_INVENTORY)],
                ['title']
            );
            $select->where(
                'warehouse_id = :warehouse_id'
            );
            $bind = ['warehouse_id' => (int)$this->factory->create()->getDefaultId()];
            $result = [$this->getConnection()->fetchOne($select, $bind)];
        }

        return $result;
    }
}
