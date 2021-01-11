<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magestore\Stocktaking\Api;

use Magento\Framework\Exception\LocalizedException;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection as StocktakingItemCollection;

/**
 * StocktakingManagementInterface for get some external data
 */
interface StocktakingManagementInterface
{
    /**
     * GetSelectBarcodeProductListJson
     *
     * @param array $productIds
     * @return mixed
     */
    public function getSelectBarcodeProductListJson(array $productIds = []);

    /**
     * Add Uncounted Product To Stocktaking
     *
     * @param int $stocktakingId
     * @return bool
     * @throws LocalizedException
     */
    public function addUncountedProductToStocktaking(int $stocktakingId);

    /**
     * Create Adjust Stock
     *
     * @param StocktakingInterface $stocktaking
     * @return bool
     */
    public function createAdjustStock(StocktakingInterface $stocktaking);

    /**
     * Process Change Quantity
     *
     * @param StocktakingInterface $stocktaking
     * @param StocktakingItemCollection $stocktakingItems
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function processChangeQuantity(
        StocktakingInterface $stocktaking,
        StocktakingItemCollection $stocktakingItems
    );
}
