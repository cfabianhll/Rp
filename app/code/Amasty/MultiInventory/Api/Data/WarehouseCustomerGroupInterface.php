<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseCustomerGroupInterface extends WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const GROUP_ID = 'group_id';

    /**#@-*/

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param int $id
     *
     * @return WarehouseCustomerGroupInterface
     */
    public function setGroupId($id);
}
