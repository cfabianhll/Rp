<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Product;

/**
 * Class AvailableQty
 */
class AvailableQty extends AbstractQty
{

    /**
     * @param array $item
     * @return int|string
     */
    protected function prepareItem(array $item)
    {
        $qty = $item['available_qty'];

        return $this->changeQty($qty);
    }
}
