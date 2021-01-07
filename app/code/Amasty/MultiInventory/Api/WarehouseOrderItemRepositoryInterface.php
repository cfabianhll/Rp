<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseOrderItemRepositoryInterface
{
    /**
     * Save item.
     *
     * @param WarehouseOrderItemInterface $warehouseItem
     *
     * @return WarehouseOrderItemInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseOrderItemInterface $warehouseItem);

    /**
     * Retrieve item.
     *
     * @param int $itemId
     *
     * @return WarehouseOrderItemInterface
     * @throws NoSuchEntityException
     */
    public function getById($itemId);

    /**
     * @param int $orderItemId
     *
     * @return WarehouseOrderItemInterface
     */
    public function getByOrderItemId($orderItemId);

    /**
     * Delete item.
     *
     * @param WarehouseOrderItemInterface $item
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(WarehouseOrderItemInterface $item);

    /**
     * Delete item by ID.
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
