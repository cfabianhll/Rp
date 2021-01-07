<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api;

use Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface;
use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseItemRepositoryInterface
{
    /**
     * Save item.
     *
     * @param WarehouseItemInterface $warehouseItem
     *
     * @return WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function save(WarehouseItemInterface $warehouseItem);

    /**
     * Add stock.
     *
     * @param \Amasty\MultiInventory\Api\Data\WarehouseItemInterface $warehouseItem
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function addStock(WarehouseItemInterface $warehouseItem);

    /**
     * @param \Amasty\MultiInventory\Api\Data\WarehouseItemApiInterface $warehouseItem
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws CouldNotSaveException
     */
    public function addStockSku(WarehouseItemApiInterface $warehouseItem);

    /**
     * Retrieve item.
     *
     * @param int $itemId
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface
     * @throws NoSuchEntityException
     */
    public function getById($itemId);

    /**
     * Load Stock item By Product ID and Warehouse ID
     *
     * @param int $productId
     * @param int $warehouseId
     *
     * @return WarehouseItemInterface
     */
    public function getByProductWarehouse($productId, $warehouseId);

    /**
     * Delete item.
     *
     * @param WarehouseItemInterface $warehouseItem
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(WarehouseItemInterface $warehouseItem);

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
     * Get stocks for product.
     *
     * @param int $id
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]
     * @throws LocalizedException
     */
    public function getStocks($id);

    /**
     * Get stocks for product.
     *
     * @param string $sku
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]|null
     * @throws LocalizedException
     */
    public function getStocksSku($sku);

    /**
     * Get products for warehouse.
     *
     * @param string $code
     *
     * @return \Amasty\MultiInventory\Api\Data\WarehouseItemInterface[]|null
     * @throws LocalizedException
     */
    public function getProducts($code);
}
