<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Logger;

/**
 * Class Logger
 */
class Logger extends \Monolog\Logger
{
    /**
     * @param $productSKU
     * @param $productId
     * @param $whName
     * @param $whCode
     * @param $adjTotal
     * @param $preTotal
     * @param string $event
     * @param string $massAction
     * @param string $manual
     * @return bool
     */
    public function infoWh(
        $productSKU,
        $productId,
        $whName,
        $whCode,
        $adjTotal,
        $preTotal,
        $event = "null",
        $massAction = "null",
        $manual = "false"
    ) {
        $array = [$productSKU, $productId, $whName, $whCode, $adjTotal, $preTotal, $event, $massAction, $manual];

        return parent::info(implode(" ", $array), []);
    }
}
