<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Api\Data;

interface WarehouseShippingInterface extends WarehouseAbstractInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const SHIPPING_METHOD = 'shipping_method';

    const RATE = 'rate';

    /**#@-*/

    /**
     * @return string
     */
    public function getShippingMethod();

    /**
     * @return double|null
     */
    public function getRate();

    /**
     * @param string $method
     *
     * @return WarehouseShippingInterface
     */
    public function setShippingMethod($method);

    /**
     * @param double $rate
     *
     * @return WarehouseShippingInterface
     */
    public function setRate($rate);
}
