<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseStoreInterface extends WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const STORE_ID = 'store_id';

    /**#@-*/

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $id
     *
     * @return WarehouseStoreInterface
     */
    public function setStoreId($id);
}
