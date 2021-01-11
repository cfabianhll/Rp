<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Api;

use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface StocktakingRepositoryInterface
 *
 * Used for stock taking repository
 */
interface StocktakingRepositoryInterface
{
    /**
     * Used for load stocktaking
     *
     * @param int $id
     * @return StocktakingInterface|null
     * @throws LocalizedException
     */
    public function load(int $id);

    /**
     * Save stocktaking
     *
     * @param StocktakingInterface $stocktaking
     * @return StocktakingInterface
     * @throws CouldNotSaveException
     */
    public function save(StocktakingInterface $stocktaking);

    /**
     * Cancel Stocktaking
     *
     * @param int $id
     * @return bool
     * @throws LocalizedException
     */
    public function cancel(int $id): bool;

    /**
     * Save Form Data
     *
     * @param int $id
     * @param array $data
     * @param int|null $updateStatus
     * @return array
     */
    public function saveFormData(int $id, array $data, int $updateStatus = null);

    /**
     * Start Counting
     *
     * @param int $stocktakingId
     * @param array $data
     * @return array
     */
    public function startCounting(int $stocktakingId, array $data);

    /**
     * Back To Prepare
     *
     * @param int $stocktakingId
     * @param array $data
     * @return array
     */
    public function backToPrepare(int $stocktakingId, array $data);

    /**
     * Complete Counting
     *
     * @param int $stocktakingId
     * @param array $data
     * @return array
     */
    public function completeCounting(int $stocktakingId, array $data);

    /**
     * Complete Stocktaking
     *
     * @param int $stocktakingId
     * @param bool $createAdjustStock
     * @return void
     */
    public function complete(int $stocktakingId, bool $createAdjustStock = false);
}
