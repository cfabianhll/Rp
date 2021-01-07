<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseImportInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseImportRepositoryInterface
{
    /**
     * Save Import.
     *
     * @param WarehouseImportInterface $item
     *
     * @return WarehouseImportInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseImportInterface $item);

    /**
     * Retrieve store.
     *
     * @param int $id
     *
     * @return WarehouseImportInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete import.
     *
     * @param WarehouseImportInterface $item
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseImportInterface $item);

    /**
     * Delete import by ID.
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
