<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface WarehouseItemApiInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const SKU = 'sku';

    const CODE = 'code';

    const QTY = 'qty';

    const AVAILABLE_QTY = 'available_qty';

    const SHIP_QTY = 'ship_qty';

    const ROOM_SHELF = 'room_shelf';

    /**#@-*/

    /**
     * @return string
     */
    public function getSku();

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return float
     */
    public function getQty();

    /**
     * @return float
     */
    public function getAvailableQty();

    /**
     * @return float
     */
    public function getShipQty();

    /**
     * @return string
     */
    public function getRoomShelf();

    /**
     * @param string $sku
     *
     * @return WarehouseItemInterface
     */
    public function setSku($sku);

    /**
     * @param string $code
     *
     * @return WarehouseItemInterface
     */
    public function setCode($code);

    /**
     * @param float $qty
     *
     * @return WarehouseItemInterface
     */
    public function setQty($qty);

    /**
     * @param float $qty
     *
     * @return WarehouseItemInterface
     */
    public function setAvailableQty($qty);

    /**
     * @param float $qty
     *
     * @return WarehouseItemInterface
     */
    public function setShipQty($qty);

    /**
     * @param string $text
     *
     * @return WarehouseItemInterface
     */
    public function setRoomShelf($text);

    /**
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getItemData();
}
