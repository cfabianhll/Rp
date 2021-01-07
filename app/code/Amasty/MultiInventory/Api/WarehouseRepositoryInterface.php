<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseRepositoryInterface
{
    /**
     * Save warehouse.
     *
     * @param WarehouseInterface $warehouse
     *
     * @return WarehouseInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseInterface $warehouse);

    /**
     * Retrieve warehouse.
     *
     * @param int $warehouseId
     *
     * @return WarehouseInterface
     * @throws NoSuchEntityException
     */
    public function getById($warehouseId);

    /**
     * Retrieve warehouse.
     *
     * @param string $warehouseCode
     *
     * @return WarehouseInterface
     * @throws NoSuchEntityException
     */
    public function getByCode($warehouseCode);

    /**
     * Delete warehouse.
     *
     * @param WarehouseInterface $warehouse
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(WarehouseInterface $warehouse);

    /**
     * Delete warehouse by ID.
     *
     * @param int $warehouseId
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($warehouseId);
}
