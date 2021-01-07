<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Product;

use Amasty\MultiInventory\Ui\Component\Listing\Column\AbstractColumn;

/**
 * Class Qty
 */
class Qty extends AbstractColumn
{
    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        return (int)$item['qty'];
    }
}
