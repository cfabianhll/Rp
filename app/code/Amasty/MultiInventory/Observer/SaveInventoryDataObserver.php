<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\DecoderInterface;

/**
 * Class SaveInventoryDataObserver
 */
class SaveInventoryDataObserver implements ObserverInterface
{
    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var System
     */
    private $system;
    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $stockRepository;
    /**
     * @var Processor
     */
    private $processor;

    public function __construct(
        WarehouseFactory $factory,
        ItemFactory $itemFactory,
        WarehouseItemRepositoryInterface $stockRepository,
        WarehouseRepositoryInterface $repository,
        System $system,
        DecoderInterface $jsonDecoder,
        Processor $processor
    ) {
        $this->factory = $factory;
        $this->itemFactory = $itemFactory;
        $this->repository = $repository;
        $this->system = $system;
        $this->jsonDecoder = $jsonDecoder;
        $this->stockRepository = $stockRepository;
        $this->processor = $processor;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->system->isMultiEnabled()) {
            return $this;
        }

        /** @var Product $product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getData('warehouses')) {
            $warehouses = $this->jsonDecoder->decode($product->getData('warehouses'));
            $defaultId = $this->factory->create()->getDefaultId();
            $allWarehouses = $this->factory->create()->getCollection()
                ->addFieldToFilter('warehouse_id', ['neq' => $defaultId])
                ->addFieldToFilter('manage', 1)
                ->getAllIds();
            if (count($warehouses)) {
                $remIds = $this->remove(array_diff($allWarehouses, array_keys($warehouses)), $product->getId());
                foreach ($warehouses as $key => $warehouse) {
                    if (!in_array($key, $remIds)) {
                        $this->change($key, $product, $warehouse);
                    }
                }
            }
        }

        if ($product->getTypeId() == Type::TYPE_SIMPLE) {
            $this->processor->reindexRow($product->getId());
        }

        return $this;
    }

    /**
     * @param array $ids
     * @param $productId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    private function remove(array $ids, $productId)
    {
        if (count($ids)) {
            foreach ($ids as $id) {
                $model = $this->repository->getById($id);
                $stockItem = $this->stockRepository->getByProductWarehouse($productId, $id);
                if ($stockItem->getId()) {
                    $model->addRemoveItem($stockItem);
                    $model->deleteItems($productId);
                }
            }
        }

        return $ids;
    }

    /**
     * @param $key
     * @param Product $product
     * @param array $warehouse
     *
     * @throws NoSuchEntityException
     */
    private function change($key, $product, $warehouse)
    {
        $model = $this->repository->getById($key);
        if (!$model->isGeneral()) {
            /** @var Item $object */
            $object = $this->itemFactory->create()
                ->setProductId($product->getId())
                ->setQty($warehouse['qty'])
                ->setRoomShelf($warehouse['room_shelf'])
                ->setBackorders($warehouse['backorders'])
                ->setStockStatus($warehouse['stock_status']);

            $model->addItem($object);
        }
    }
}
