<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Stocktaking;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterfaceFactory;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem as StocktakingItemResource;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection as ItemCollection;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\CollectionFactory as ItemCollectionFactory;

/**
 * Class StocktakingItemRepository
 *
 * Used for stocktaking item repository
 */
class StocktakingItemRepository implements StocktakingItemRepositoryInterface
{
    /**
     * @var StocktakingItemInterfaceFactory
     */
    protected $stocktakingItemFactory;

    /**
     * @var StocktakingItemResource
     */
    protected $stocktakingItemResource;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * StocktakingItemRepository constructor.
     *
     * @param StocktakingItemInterfaceFactory $stocktakingItemFactory
     * @param StocktakingItemResource $stocktakingItemResource
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(
        StocktakingItemInterfaceFactory $stocktakingItemFactory,
        StocktakingItemResource $stocktakingItemResource,
        ItemCollectionFactory $itemCollectionFactory
    ) {
        $this->stocktakingItemFactory = $stocktakingItemFactory;
        $this->stocktakingItemResource = $stocktakingItemResource;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function save(StocktakingItemInterface $stocktakingItem)
    {
        try {
            return $this->stocktakingItemResource->save($stocktakingItem);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
    }

    /**
     * Import row to stocktake
     *
     * @param ProductModel $productModel
     * @param array $row
     * @param StocktakingInterface $stocktaking
     * @return boolean
     * @throws LocalizedException
     */
    public function importData(ProductModel $productModel, array $row, StocktakingInterface $stocktaking)
    {
        $productData = [];
        $productData[StocktakingItemInterface::STOCKTAKING_ID] = $stocktaking->getId();
        $productData[StocktakingItemInterface::PRODUCT_ID] = $productModel->getId();
        $productData[StocktakingItemInterface::PRODUCT_NAME] = $productModel->getName();
        $productData[StocktakingItemInterface::PRODUCT_SKU] = $productModel->getSku();
        $productData[StocktakingItemInterface::QTY_IN_SOURCE] = $productModel->getQuantity();
        if ($stocktaking->getStatus() == StocktakingInterface::STATUS_PREPARING) {
            $productData[StocktakingItemInterface::COUNTED_QTY] = 0;
            $productData[StocktakingItemInterface::DIFFERENCE_REASON] = '';
        } else {
            $productData[StocktakingItemInterface::COUNTED_QTY] = $row[1];
            $productData[StocktakingItemInterface::DIFFERENCE_REASON] = $row[2];
        }
        $itemModel = $this->itemCollectionFactory->create()->addFieldToFilter(
            StocktakingItemInterface::PRODUCT_ID,
            $productModel->getId()
        )->addFieldToFilter(
            StocktakingItemInterface::STOCKTAKING_ID,
            $stocktaking->getId()
        )->getFirstItem();
        if ($itemModel && $itemModel->getId()) {
            $id = $itemModel->getId();
            $itemModel->setData($productData);
            $itemModel->setData(StocktakingItemInterface::ID, $id);
        } else {
            $itemModel = $this->stocktakingItemFactory->create();
            $itemModel->setData($productData);
        }
        try {
            $this->stocktakingItemResource->save($itemModel);
            $importStatus = true;
        } catch (LocalizedException $exception) {
            $importStatus = false;
        }
        return $importStatus;
    }

    /**
     * Get Stocktaking item collection by Stocktaking id
     *
     * @param int $stocktakingId
     * @return ItemCollection
     */
    public function getListByStocktakingId(int $stocktakingId)
    {
        /** @var ItemCollection $itemCollection */
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->addFieldToFilter(
            StocktakingItemInterface::STOCKTAKING_ID,
            ['eq' => $stocktakingId]
        );
        return $itemCollection;
    }

    /**
     * @inheritDoc
     */
    public function deleteByStocktakingId(int $stocktakingId)
    {
        /** @var StocktakingItemCollection $stocktakingItems */
        $stocktakingItems = $this->getListByStocktakingId($stocktakingId);

        foreach ($stocktakingItems as $item) {
            $this->stocktakingItemResource->delete($item);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setStocktakingItems(int $stocktakingId, array $stocktakingItems)
    {
        $this->deleteByStocktakingId($stocktakingId);

        foreach ($stocktakingItems as $item) {
            /** @var StocktakingItemInterface $itemModel */
            $itemModel = $this->stocktakingItemFactory->create();
            $itemModel->setData($item);
            $this->save($itemModel);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function addStocktakingItems(int $stocktakingId, array $stocktakingItems)
    {
        // Get list current stocktaking items
        $currentStocktakingItems = [];
        foreach ($this->getListByStocktakingId($stocktakingId) as $item) {
            $currentStocktakingItems[$item->getProductId()] = $item;
        }

        // Add & Update stocktaking items
        foreach ($stocktakingItems as $item) {
            // Update exiting item
            if (isset($currentStocktakingItems[$item[StocktakingItemInterface::PRODUCT_ID]])) {
                /** @var StocktakingItemInterface $currentItem */
                $currentItem = $currentStocktakingItems[$item[StocktakingItemInterface::PRODUCT_ID]];
                $id = $currentItem->getId();
                $currentItem->setData($item);
                $currentItem->setId($id);
                $this->save($currentItem);
            } else {
                /** @var StocktakingItemInterface $itemModel */
                $itemModel = $this->stocktakingItemFactory->create();
                $itemModel->setData($item);
                $this->save($itemModel);
            }
        }
        return true;
    }
}
