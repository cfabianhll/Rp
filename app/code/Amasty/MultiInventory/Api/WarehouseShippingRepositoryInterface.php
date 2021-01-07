<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseShippingInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseShippingRepositoryInterface
{
    /**
     * Save shipping.
     *
     * @param WarehouseShippingInterface $item
     *
     * @return WarehouseShippingInterface
     * @throws CouldNotSaveException
     */
    public function save(Data\WarehouseShippingInterface $item);

    /**
     * Retrieve shipping.
     *
     * @param int $id
     *
     * @return WarehouseShippingInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete shipping.
     *
     * @param WarehouseShippingInterface $item
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WarehouseShippingInterface $item);

    /**
     * Delete shipping by ID.
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
