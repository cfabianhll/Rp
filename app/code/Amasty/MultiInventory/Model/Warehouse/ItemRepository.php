<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface;
use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item as ItemResource;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ItemRepository
 */
class ItemRepository implements WarehouseItemRepositoryInterface
{
    /**
     * @var ItemResource
     */
    protected $resource;

    /**
     * @var ItemFactory
     */
    protected $factory;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var array
     */
    private $stockItems = [];

    /**
     * @var CollectionFactory
     */
    private $stockCollection;

    /**
     * @var WarehouseCollectionFactory
     */
    private $warehouseCollectionFactory;

    /**
     * ItemRepository constructor.
     *
     * @param ItemResource $resource
     * @param ItemFactory $factory
     * @param CollectionFactory $stockCollection
     * @param WarehouseCollectionFactory $warehouseCollectionFactory
     * @param WarehouseFactory $warehouseFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Processor $processor
     */
    public function __construct(
        ItemResource $resource,
        ItemFactory $factory,
        CollectionFactory $stockCollection,
        WarehouseCollectionFactory $warehouseCollectionFactory,
        WarehouseFactory $warehouseFactory,
        ProductRepositoryInterface $productRepository,
        Processor $processor
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->processor = $processor;
        $this->warehouseFactory = $warehouseFactory;
        $this->productRepository = $productRepository;
        $this->stockCollection = $stockCollection;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseItemInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function addStock(WarehouseItemInterface $item)
    {
        try {
            if (!$item->getId()) {
                /** @var Item $stockItem */
                $stockItem = $this->getByProductWarehouse($item->getProductId(), $item->getWarehouseId());

                if ($stockItem->getId()) {
                    $stockItem->setQty($item->getQty());
                    $stockItem->setRoomShelf($item->getRoomShelf());
                    $item = $stockItem;
                }
            }
            $item->recalcAvailable();
            $this->resource->save($item);
            $this->setStockItem($item);
            $this->processor->reindexRow($item->getProductId());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function addStockSku(WarehouseItemApiInterface $item)
    {
        try {
            $newData = $item->getItemData();
            $sendItem = $this->addStock($this->factory->create()->setData($newData));
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $sendItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Store with id "%1" does not exist.', $id));
        }
        $this->setStockItem($model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getByProductWarehouse($productId, $warehouseId)
    {
        if (!isset($this->stockItems[$warehouseId][$productId])) {
            $this->stockItems[$warehouseId][$productId] = $this->stockCollection->create()
                ->loadProductWarehouse($productId, $warehouseId);
        }

        return $this->stockItems[$warehouseId][$productId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseItemInterface $item)
    {
        try {
            $productId = $item->getProductId();
            $warehouseId = $item->getWarehouseId();

            if (isset($this->stockItems[$warehouseId][$productId])) {
                unset($this->stockItems[$warehouseId][$productId]);
            }
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the warehouse store: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * {@inheritdoc}
     */
    public function getStocks($id)
    {
        return $this->stockCollection->create()
            ->addFieldToFilter(WarehouseItemInterface::PRODUCT_ID, $id)
            ->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getStocksSku($sku)
    {
        if ($id = $this->productRepository->get($sku)->getId()) {
            return $this->getStocks($id);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts($code)
    {
        $collectWh = $this->warehouseCollectionFactory->create()
            ->addFieldToFilter(WarehouseInterface::CODE, $code);

        if ($id = $collectWh->getFirstItem()->getId()) {
            return $this->stockCollection->create()
                ->addFieldToFilter(WarehouseItemInterface::WAREHOUSE_ID, $id)
                ->getItems();
        }

        return null;
    }

    /**
     * @param WarehouseItemInterface $stockItem
     */
    private function setStockItem(WarehouseItemInterface $stockItem)
    {
        $this->stockItems[$stockItem->getWarehouseId()][$stockItem->getProductId()] = $stockItem;
    }
}
