<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseImportInterface extends WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ITEM_ID = 'item_id';

    const PRODUCT_ID = 'product_id';

    const QTY = 'qty';

    const NEW_QTY = 'new_qty';

    const IMPORT_NUMBER = 'import_number';

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
    public function getNewQty();

    /**
     * @return int
     */
    public function getImportNumber();

    /**
     * @param int $id
     *
     * @return WarehouseImportInterface
     */
    public function setProductId($id);

    /**
     * @param float $qty
     *
     * @return WarehouseImportInterface
     */
    public function setQty($qty);

    /**
     * @param float $qty
     *
     * @return WarehouseImportInterface
     */
    public function setNewQty($qty);

    /**
     * @param int $number
     *
     * @return WarehouseImportInterface
     */
    public function setImportNumber($number);
}
