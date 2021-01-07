<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

/**
 * Class Warehouse
 */
class Warehouse extends AbstractDb
{
    const AMASTY_INVENTORY = 'amasty_multiinventory_warehouse';

    const AMASTY_INVENTORY_STORE = 'amasty_multiinventory_store';

    const AMASTY_INVENTORY_GROUP = 'amasty_multiinventory_customer_group';

    const AMASTY_INVENTORY_ITEM = 'amasty_multiinventory_warehouse_item';

    const AMASTY_INVENTORY_SHIPPING = 'amasty_multiinventory_warehouse_shipping';

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_multiinventory_warehouse', 'warehouse_id');
    }

    /**
     * Warehouse constructor.
     * @param Context $context
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        $connectionName = null
    ) {
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $warehouse
     *
     * @return array
     */
    public function getItems($warehouse)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable('amasty_multiinventory_warehouse_item')],
            ['product_id']
        )->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$warehouse->getId()];
        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param $warehouse
     *
     * @return array
     */
    public function getItemsToGrid($warehouse)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable('amasty_multiinventory_warehouse_item')],
            ['product_id', 'qty', 'backorders', 'stock_status']
        )->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$warehouse->getId()];
        $data = $this->getConnection()->fetchAssoc($select, $bind);

        return $data;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getStoreIds($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_STORE)],
            ['store_id']
        )->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$id];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getWarehousesByStoreId($storeId)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_STORE)],
            ['warehouse_id']
        )->join(
            ['wm' => $this->getTable(self::AMASTY_INVENTORY)],
            'w.warehouse_id = wm.warehouse_id AND wm.manage = 1',
            null
        )->where(
            'w.store_id = :store_id'
        );
        $bind = ['store_id' => (int)$storeId];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getShippingsCodes($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_SHIPPING)],
            ['shipping_method']
        )->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$id];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getGroupIds($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_GROUP)],
            ['group_id']
        )->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$id];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getItemIds($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_ITEM)],
            ['product_id']
        )->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$id];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param $warehouse
     *
     * @return array
     */
    public function getTotalSku($warehouse)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_ITEM)],
            ['size' => new \Zend_Db_Expr(sprintf('COUNT(%s)', 'product_id'))]
        );
        $select->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$warehouse->getId()];

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @param $warehouse
     * @param string $field
     *
     * @return string
     */
    public function getTotalQty($warehouse, $field = 'qty')
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_ITEM)],
            ['size' => new \Zend_Db_Expr(sprintf('SUM(%s)', $field))]
        );
        $select->where(
            'warehouse_id = :warehouse_id'
        );
        $bind = ['warehouse_id' => (int)$warehouse->getId()];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $productId
     * @param int $id
     *
     * @return string
     */
    public function getAllTotalQty($productId, $id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY_ITEM)],
            ['size' => new \Zend_Db_Expr(sprintf('SUM(%s)', 'qty'))]
        );
        $select->where(
            'warehouse_id <> :warehouse_id and product_id = :product_id'
        );
        $bind = [
            'warehouse_id' => (int)$id,
            'product_id' => (int)$productId
        ];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public function getDefaultId($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable('amasty_multiinventory_warehouse')],
            ['warehouse_id']
        )->where(
            'stock_id = :stock_id'
        );
        $bind = ['stock_id' => (int)$id];
        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getWhNotActive($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY)],
            ['warehouse_id']
        );
        $select->where(
            'manage = :manage and warehouse_id <> :warehouse_id'
        );
        $bind = ['manage' => 0, 'warehouse_id' => $id];
        $data = $this->getConnection()->fetchCol($select, $bind);
        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getWhCodes($id)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getTable(self::AMASTY_INVENTORY)],
            ['code', 'warehouse_id']
        );
        $select->where(
            'warehouse_id <> :warehouse_id'
        );
        $bind = ['warehouse_id' => $id];
        $data = $this->getConnection()->fetchPairs($select, $bind);
        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * update Warehouses
     */
    public function updateManages()
    {
        $this->getConnection()->update($this->getMainTable(), ['manage' => 0], 'stock_id IS NULL');
    }

    /**
     * @param int $id
     *
     * @param null $connection
     */
    public function setDatafromInventory($id, $connection = null)
    {
        if (!$connection) {
            $connection = $this;
        }
        $columns = [
            'product_id' => 'csi.product_id',
            'warehouse_id' => 'amw.warehouse_id',
            'qty' => new \Zend_Db_Expr('IFNULL(csi.qty,0)'),
            'available_qty' => new \Zend_Db_Expr('(IFNULL(csi.qty,0) -  IFNULL(SUM(soi.qty_ordered),0))'),
            'ship_qty' => new \Zend_Db_Expr('IFNULL(SUM(soi.qty_ordered),0)')
        ];
        $select = $this->getConnection()->select();
        $select->from(
            ['csi' => $connection->getTable('cataloginventory_stock_item')],
            $columns
        )->joinLeft(
            ['soi' => $connection->getTable('sales_order_item')],
            'soi.product_id = csi.product_id'
            . ' and soi.qty_shipped = 0 and soi.qty_invoiced > 0 and soi.product_type IN ("simple")',
            []
        )->joinLeft(
            ['amw' => $connection->getTable('amasty_multiinventory_warehouse')],
            'amw.stock_id = csi.stock_id',
            []
        )->joinLeft(
            ['cpe' => $connection->getTable('catalog_product_entity')],
            'cpe.entity_id = csi.product_id',
            []
        )
            ->where(sprintf('csi.stock_id=%s and cpe.type_id="simple"', $id))
            ->group(['csi.product_id']);
        $query = $select->insertFromSelect(
            $connection->getTable('amasty_multiinventory_warehouse_item'),
            array_keys($columns)
        );
        $connection->getConnection()->query($query);
    }

    /**
     * @param AbstractModel $object
     * @param int $value
     * @param null $field
     *
     * @return bool|int
     * @throws LocalizedException
     */
    private function getWarehouseId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(WarehouseInterface::class);

        if (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }
        $entityId = $value;

        if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $select->reset(Select::COLUMNS)
                ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
                ->limit(1);
            $result = $this->getConnection()->fetchCol($select);
            $entityId = count($result) ? $result[0] : false;
        }
        return $entityId;
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $warehouseId = $this->getWarehouseId($object, $value, $field);
        if ($warehouseId) {
            $this->entityManager->load($object, $warehouseId);
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this|AbstractDb
     * @throws \Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
