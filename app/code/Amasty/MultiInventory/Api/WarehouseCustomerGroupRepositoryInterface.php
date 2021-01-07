<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseCustomerGroupRepositoryInterface
{
    /**
     * Save customer group.
     *
     * @param WarehouseCustomerGroupInterface $item
     *
     * @return WarehouseCustomerGroupInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseCustomerGroupInterface $item);

    /**
     * Retrieve customer group.
     *
     * @param int $id
     *
     * @return WarehouseCustomerGroupInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete customer group.
     *
     * @param WarehouseCustomerGroupInterface $item
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(WarehouseCustomerGroupInterface $item);

    /**
     * Delete customer group by ID.
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($id);
}
