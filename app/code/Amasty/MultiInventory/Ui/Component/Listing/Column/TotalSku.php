<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column;

/**
 * Class TotalSku
 */
class TotalSku extends AbstractColumn
{
    /**
     * Get data
     *
     * @param array $item
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareItem(array $item)
    {
        return $this->getTotalSku($item['warehouse_id']);
    }
}
