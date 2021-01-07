<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse;

use Amasty\MultiInventory\Model\WarehouseFactory;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;

class Item extends AbstractDb
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $productCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableProduct;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Amasty\MultiInventory\Model\ConfigurableProductIdsReindexRegistry
     */
    private $idsReindexRegistry;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_multiinventory_warehouse_item', 'item_id');
    }

    /**
     * Item constructor.
     *
     * @param Context $context
     * @param EntityManager $entityManager
     * @param WarehouseFactory $factory
     * @param CacheContext $cacheContext
     * @param ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurableProduct
     * @param LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        WarehouseFactory $factory,
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableProduct,
        LoggerInterface $logger,
        \Amasty\MultiInventory\Model\ConfigurableProductIdsReindexRegistry $idsReindexRegistry,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->entityManager = $entityManager;
        $this->factory = $factory;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->productCollection = $productCollection;
        $this->productRepository = $productRepository;
        $this->configurableProduct = $configurableProduct;
        $this->logger = $logger;
        $this->idsReindexRegistry = $idsReindexRegistry;
    }

    /**
     * @param AbstractModel $object
     * @param int|string $value
     * @param string|null $field
     * @return \Amasty\MultiInventory\Model\Warehouse\Item
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        return $this->entityManager->load($object, $value);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     * @throws Exception
     */
    public function save(AbstractModel $object)
    {
        if ($object->isStockStatusChanged()) {
            $this->cleanCache($object);
        }

        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     * @throws Exception
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }

    /**
     * @param int $warehouseId
     * @param string $field
     *
     * @return string
     * @throws LocalizedException
     */
    public function getTotalQty($warehouseId, $field = 'qty')
    {
        $select = $this->getStockSelect($warehouseId);
        $select->columns(['size' => sprintf('SUM(%s)', $field)]);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * If don't have assigned warehouses for product, will return 1. Because default warehouse
     *
     * @param int|null $warehouseId
     * @param int|null $productId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getTotalSku($warehouseId = null, $productId = null)
    {
        $select = $this->getStockSelect($warehouseId, $productId);
        $select->columns(['size' => 'COUNT(product_id)']);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @param int $productId
     * @param int|null $warehouseId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getProductStockData($productId, $warehouseId = null)
    {
        if (!$warehouseId) {
            $warehouseId = $this->factory->create()->getDefaultId();
        }
        $select = $this->getStockSelect($warehouseId, $productId);
        $select->columns();

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param null|int $warehouseId
     * @param null|int $productId
     *
     * @return Select
     * @throws LocalizedException
     */
    private function getStockSelect($warehouseId = null, $productId = null)
    {
        $select = $this->getConnection()->select()->from(
            ['wi' => $this->getMainTable()],
            []
        );

        if ($productId !== null) {
            $select->where('wi.product_id = ?', $productId);
        }

        if ($warehouseId !== null) {
            $select->where('wi.warehouse_id = ?', $warehouseId);
        }

        return $select;
    }

    /**
     * @param $productId
     *
     * @return array
     * @throws LocalizedException
     */
    public function getItems($productId)
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getMainTable()],
            ['w.warehouse_id', 'wh.title', 'w.qty']
        )->where(
            'w.product_id = :product_id'
        )->joinLeft(
            ['wh' => $this->getTable('amasty_multiinventory_warehouse')],
            'wh.warehouse_id = w.warehouse_id',
            ['wh.title']
        )->where(sprintf('wh.manage=%s and w.warehouse_id<>%s', 1, $this->factory->create()->getDefaultId()));
        $bind = ['product_id' => (int)$productId];

        return $this->getConnection()->fetchAssoc($select, $bind);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getItemsExport()
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getMainTable()],
            ['cpe.sku', 'wh.warehouse_id', 'wh.code', 'w.qty']
        )
            ->joinLeft(
                ['wh' => $this->getTable('amasty_multiinventory_warehouse')],
                'wh.warehouse_id = w.warehouse_id'
            )
            ->joinLeft(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'cpe.entity_id = w.product_id'
            );
        $bind = [];

        return $this->getConnection()->fetchAll($select, $bind);
    }

    public function getItemsOnTotalStock()
    {
        $select = $this->getConnection()->select()->from(
            ['w' => $this->getMainTable()],
            ['e.sku']
        )
            ->joinLeft(
                ['e' => $this->getTable('catalog_product_entity')],
                'w.product_id = e.entity_id'
            )
            ->group('product_id')
            ->having('COUNT(warehouse_id) = 1');

        return $this->getConnection()->fetchCol($select, 'sku');
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function deleteItems()
    {
        return $this->getConnection()->delete($this->getMainTable());
    }

    protected function cleanCache($object)
    {
        $select = $this->productCollection->getSelect()->reset()
            ->distinct(true)
            ->from($this->productCollection->getTable('catalog_category_product'), ['category_id'])
            ->where('product_id = ?', $object->getProductId());
        $affectedCategories = $this->productCollection->getConnection()->fetchCol($select);
        $parentProducts = $this->configurableProduct->getParentIdsByChild($object->getProductId());

        $this->cacheContext->registerEntities(Category::CACHE_TAG, $affectedCategories);
        $this->cacheContext->registerEntities(Product::CACHE_TAG, [$object->getProductId()]);

        if ($parentProducts) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $parentProducts);
            $this->idsReindexRegistry->addIdsToReindex($parentProducts);
            // Clean cache of parent products (for associated blocks such as option selector).
            try {
                foreach ($parentProducts as $parentProduct) {
                    /** @var Product $product */
                    $product = $this->productRepository->getById($parentProduct);
                    $product->cleanModelCache();
                }
            } catch (NoSuchEntityException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }
}
