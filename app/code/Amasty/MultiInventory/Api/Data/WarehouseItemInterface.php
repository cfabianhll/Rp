<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseItemInterface extends WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ITEM_ID = 'item_id';

    const PRODUCT_ID = 'product_id';

    const QTY = 'qty';

    const AVAILABLE_QTY = 'available_qty';

    const SHIP_QTY = 'ship_qty';

    const ROOM_SHELF = 'room_shelf';

    const STOCK_STATUS = 'stock_status';

    const BACKORDERS = 'backorders';

    /**#@-*/

    /**
     * @return int
     */
    public function getProductId();

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
     * @param int $id
     *
     * @return WarehouseItemInterface
     */
    public function setProductId($id);

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
     * @return int
     */
    public function getStockStatus();

    /**
     * @param int $stockStatus
     *
     * @return WarehouseItemInterface
     */
    public function setStockStatus($stockStatus);

    /**
     * @return int
     */
    public function getBackorders();

    /**
     * @param int $backorders
     *
     * @return WarehouseItemInterface
     */
    public function setBackorders($backorders);
}
