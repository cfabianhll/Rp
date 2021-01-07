<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';

    const WAREHOUSE_ID = 'warehouse_id';

    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getWarehouseId();

    /**
     * @param int $id
     *
     * @return WarehouseAbstractInterface
     */
    public function setId($id);

    /**
     * @param int $id
     *
     * @return WarehouseAbstractInterface
     */
    public function setWarehouseId($id);
}
