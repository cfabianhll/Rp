<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\DataProvider;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\ResourceConnection;
use Magento\Ui\DataProvider\AbstractDataProvider;

class StockDataProvider extends AbstractDataProvider
{
    const WAREHOUSE_ITEMS = 'wis';

    /**
     * Product collection
     *
     * @var Collection
     */
    protected $collection;

    private $list;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var System
     */
    private $helper;

    /**
     * StockDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resource
     * @param WarehouseFactory $factory
     * @param System $helper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        ResourceConnection $resource,
        WarehouseFactory $factory,
        System $helper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create()->addFieldToFilter('type_id', 'simple');
        $this->resource = $resource;
        $this->factory = $factory;
        $this->helper = $helper;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $collection = $this->getCollection()->addFieldToFilter('type_id', 'simple');
            $collection->load();
        }
        $items = $this->getCollection()->toArray();

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * @param Filter $filter
     *
     * @return bool
     * @throws \Zend_Db_Select_Exception
     */
    public function addFilter(Filter $filter)
    {
        if ($this->calcLowStock($filter)) {
            return true;
        }
        list($id, $field) = $this->inWarehouses($filter->getField());

        if ($id) {
            $this->calcQty($id, $field, $filter);
        } else {
            $this->getCollection()->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getWarehouses()
    {
        if ($this->list == null) {
            $collection = $this->factory->create()
                ->getCollection()
                ->clear()
                ->addFieldToSelect('warehouse_id')
                ->addFieldToSelect('code')
                ->toArray();
            $this->list = $collection['items'];
        }

        return $this->list;
    }

    /**
     * @param string $item
     * @return array
     */
    private function inWarehouses($item)
    {
        $id = 0;
        $wh = $item;
        $field = 'qty';
        if (strpos($item, '_shelf') !== false) {
            $wh = str_replace('_shelf', '', $item);
            $field = 'room_shelf';
        }
        $list = $this->getWarehouses();

        foreach ($list as $record) {
            if ($record['code'] == $wh) {
                $id = $record['warehouse_id'];
                break;
            }
        }

        return [$id, $field];
    }

    /**
     * @param int $id
     * @param string $field
     * @param Filter $filter
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function calcQty($id, $field, $filter)
    {
        $select = $this->getCollection()
            ->getSelect();
        $parts = array_keys($this->getCollection()
            ->getSelect()->getPart(\Zend_Db_Select::FROM));
        if (!in_array(self::WAREHOUSE_ITEMS, $parts)) {
            $select->joinLeft(
                ['wis' => $this->resource->getTableName('amasty_multiinventory_warehouse_item')],
                sprintf(
                    '%s.product_id = e.entity_id and %s.warehouse_id="%s"',
                    self::WAREHOUSE_ITEMS,
                    self::WAREHOUSE_ITEMS,
                    $id
                )
            );
        }
        $where = $this->getCollection()
            ->getConnection()
            ->prepareSqlCondition(
                self::WAREHOUSE_ITEMS . "." . $field,
                [$filter->getConditionType() => $filter->getValue()]
            );
        $select->where($where);
    }

    /**
     * @param Filter $filter
     *
     * @return bool
     * @throws \Zend_Db_Select_Exception
     */
    public function calcLowStock($filter)
    {
        if ($filter->getField() == 'low_stock') {
            $lowstock = $this->helper->getLowStock();
            $id = $filter->getValue();
            $select = $this->getCollection()
                ->getSelect();
            $parts = array_keys($this->getCollection()
                ->getSelect()->getPart(\Zend_Db_Select::FROM));
            if (!in_array(self::WAREHOUSE_ITEMS, $parts)) {
                $select->joinLeft(
                    [
                        'wis' => $this->resource->getTableName('amasty_multiinventory_warehouse_item')
                    ],
                    sprintf(
                        '%s.product_id = e.entity_id and %s.warehouse_id="%s"',
                        self::WAREHOUSE_ITEMS,
                        self::WAREHOUSE_ITEMS,
                        $id
                    )
                );
            }
            $where = sprintf('%s.available_qty <= %s', self::WAREHOUSE_ITEMS, $lowstock);
            $select->where($where);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getSqlItems()
    {
        $sql = $this->getCollection()->getSelectSql(true);

        return $this->collection->getConnection()->fetchAll($sql);
    }
}
