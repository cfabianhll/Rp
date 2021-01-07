<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseStoreInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseStoreRepositoryInterface
{
    /**
     * Save store.
     *
     * @param WarehouseStoreInterface $item
     *
     * @return WarehouseStoreInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseStoreInterface $item);

    /**
     * Retrieve store.
     *
     * @param int $id
     *
     * @return WarehouseStoreInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete store.
     *
     * @param WarehouseStoreInterface $item
     *
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(WarehouseStoreInterface $item);

    /**
     * Delete store by ID.
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($id);
}
