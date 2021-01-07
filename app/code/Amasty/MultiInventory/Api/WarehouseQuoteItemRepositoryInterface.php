<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartItemInterface;

interface WarehouseQuoteItemRepositoryInterface
{
    /**
     * Save item.
     *
     * @param WarehouseQuoteItemInterface $warehouseItem
     *
     * @return WarehouseQuoteItemInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseQuoteItemInterface $warehouseItem);

    /**
     * Retrieve item.
     *
     * @param int $itemId
     *
     * @return WarehouseQuoteItemInterface
     * @throws NoSuchEntityException
     */
    public function getById($itemId);

    /**
     * Delete item.
     *
     * @param WarehouseQuoteItemInterface $warehouseItem
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(WarehouseQuoteItemInterface $warehouseItem);

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

    /**
     * @param CartItemInterface $quoteItem
     *
     * @return int|null
     */
    public function getWarehouseIdByItem($quoteItem);
}
