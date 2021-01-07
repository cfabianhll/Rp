<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

/**
 * Class TotalAvailable
 */
class TotalAvailable extends AbstractColumn
{
    /**
     * Get data
     *
     * @param array $item
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareItem(array $item)
    {
        return (int)$this->getTotalQty($item['warehouse_id'], 'available_qty');
    }
}
