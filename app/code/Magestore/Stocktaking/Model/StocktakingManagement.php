<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magestore\Stocktaking\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\Collection
    as SourceProductCollection;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\CollectionFactory
    as SourceProductCollectionFactory;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection as StocktakingItemCollection;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * StocktakingManagement: Process stocktaking
 */
class StocktakingManagement implements \Magestore\Stocktaking\Api\StocktakingManagementInterface
{
    /**
     * @var SourceProductCollectionFactory
     */
    protected $sourceProductCollectionFactory;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var StocktakingItemRepositoryInterface
     */
    protected $stocktakingItemRepository;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var SourceItemConvert
     */
    protected $sourceItemConvert;
    /**
     * @var SourceItemsSaveInterface
     */
    protected $sourceItemsSave;

    /**
     * StocktakingManagement constructor.
     *
     * @param SourceProductCollectionFactory $sourceProductCollectionFactory
     * @param SerializerInterface $serializer
     * @param StocktakingItemRepositoryInterface $stocktakingItemRepository
     * @param EventManagerInterface $eventManager
     * @param SourceItemConvert $sourceItemConvert
     * @param SourceItemsSaveInterface $sourceItemsSave
     */
    public function __construct(
        SourceProductCollectionFactory $sourceProductCollectionFactory,
        SerializerInterface $serializer,
        StocktakingItemRepositoryInterface $stocktakingItemRepository,
        EventManagerInterface $eventManager,
        SourceItemConvert $sourceItemConvert,
        SourceItemsSaveInterface $sourceItemsSave
    ) {
        $this->sourceProductCollectionFactory = $sourceProductCollectionFactory;
        $this->serializer = $serializer;
        $this->stocktakingItemRepository = $stocktakingItemRepository;
        $this->eventManager = $eventManager;
        $this->sourceItemConvert = $sourceItemConvert;
        $this->sourceItemsSave = $sourceItemsSave;
    }

    /**
     * @inheritDoc
     */
    public function getSelectBarcodeProductListJson(array $productIds = [])
    {
        $result = [];
        /** @var SourceProductCollection $collection */
        $collection = $this->sourceProductCollectionFactory->create();
        if (count($productIds)) {
            $collection->addFieldToFilter('entity_id', ['in' => array_values($productIds)]);
        }
        if (!$collection->isLoaded()) {
            $collection->load();
        }
        $items = $collection->toArray();

        foreach ($items as $item) {
            if (isset($item['quantity'])) {
                $item['quantity'] = (float)$item['quantity'];
            }
            if (isset($item['barcode'])) {
                $barcodes = explode(',', (string)$item['barcode']);
                foreach ($barcodes as $barcode) {
                    $result[$barcode] = $item;
                }
            }
        }

        return $this->serializer->serialize($result);
    }

    /**
     * @inheritDoc
     */
    public function addUncountedProductToStocktaking(int $stocktakingId)
    {
        $stocktakingItems = [];
        $uncountedProducts = $this->sourceProductCollectionFactory->create()
            ->getUncountedSkuStocktaking($stocktakingId);

        foreach ($uncountedProducts as $product) {
            if ((float) $product->getQtyInSource() != 0) {
                $stocktakingItems[] = [
                    StocktakingItemInterface::STOCKTAKING_ID => $stocktakingId,
                    StocktakingItemInterface::PRODUCT_ID => (int) $product->getId(),
                    StocktakingItemInterface::PRODUCT_NAME => $product->getName(),
                    StocktakingItemInterface::PRODUCT_SKU => $product->getSku(),
                    StocktakingItemInterface::QTY_IN_SOURCE => (float) $product->getQtyInSource(),
                    StocktakingItemInterface::COUNTED_QTY => 0,
                ];
            }
        }

        return $this->stocktakingItemRepository->addStocktakingItems($stocktakingId, $stocktakingItems);
    }

    /**
     * @inheritDoc
     */
    public function createAdjustStock(StocktakingInterface $stocktaking)
    {
        /** @var StocktakingItemCollection $stocktakingItems */
        $stocktakingItems = $this->stocktakingItemRepository->getListByStocktakingId($stocktaking->getId());

        $eventData = new \Magento\Framework\DataObject(
            [
                'stocktaking' => $stocktaking,
                'stocktaking_items' => $stocktakingItems,
                'is_created_adjust_stock' => false
            ]
        );
        $this->eventManager->dispatch(
            'stocktaking_create_adjust_stock',
            ['event_data' => $eventData]
        );

        $isCreatedAdjustStock = $eventData->getIsCreatedAdjustStock();

        if (!$isCreatedAdjustStock) {
            $this->processChangeQuantity($stocktaking, $stocktakingItems);
            $isCreatedAdjustStock = true;
        }
        return $isCreatedAdjustStock;
    }

    /**
     * @inheritDoc
     */
    public function processChangeQuantity(
        StocktakingInterface $stocktaking,
        StocktakingItemCollection $stocktakingItems
    ) {
        if (!$stocktaking->getId() || !$stocktaking->getSourceCode()) {
            return;
        }
        $sourceItems = [];
        $packItems = 0;
        $i = 0;

        foreach ($stocktakingItems as $item) {
            $sourceItem = [
                Sources::COL_SOURCE_CODE => $stocktaking->getSourceCode(),
                Sources::COL_SKU => $item->getProductSku(),
                Sources::COL_QTY => $item->getCountedQty(),
                Sources::COL_STATUS => 1
            ];
            $sourceItems[$packItems][] = $sourceItem;
            $i++;
            if ($i == 500) {
                $i = 0;
                $packItems++;
            }
        }

        if (!empty($sourceItems)) {
            foreach ($sourceItems as $items) {
                $items = $this->sourceItemConvert->convert($items);
                $this->sourceItemsSave->execute($items);
            }
        }
    }
}
