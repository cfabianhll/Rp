<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Api;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Exception\LocalizedException;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection as StocktakingItemCollection;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Interface StocktakingItemRepositoryInterface
 *
 * Used for stock taking item repository
 */
interface StocktakingItemRepositoryInterface
{

    /**
     * Save stocktaking item
     *
     * @param StocktakingItemInterface $stocktakingItem
     * @return StocktakingItemInterface
     * @throws CouldNotSaveException
     */
    public function save(StocktakingItemInterface $stocktakingItem);

    /**
     * Import row to stocktake
     *
     * @param ProductModel $productModel
     * @param array $row
     * @param StocktakingInterface $stocktaking
     * @return boolean
     * @throws LocalizedException
     */
    public function importData(ProductModel $productModel, array $row, StocktakingInterface $stocktaking);

    /**
     * Get Stocktaking item collection by Stocktaking id
     *
     * @param int $stocktakingId
     * @return StocktakingItemCollection
     */
    public function getListByStocktakingId(int $stocktakingId);

    /**
     * Delete By Stocktaking Id
     *
     * @param int $stocktakingId
     * @return bool
     * @throws LocalizedException
     */
    public function deleteByStocktakingId(int $stocktakingId);

    /**
     * Set Stocktaking Items For Stocktaking
     *
     * @param int $stocktakingId
     * @param array $stocktakingItems
     * @return bool
     * @throws LocalizedException
     */
    public function setStocktakingItems(int $stocktakingId, array $stocktakingItems);

    /**
     * Add Stocktaking Items to Stocktaking
     *
     * @param int $stocktakingId
     * @param array $stocktakingItems
     * @return bool
     * @throws LocalizedException
     */
    public function addStocktakingItems(int $stocktakingId, array $stocktakingItems);
}
