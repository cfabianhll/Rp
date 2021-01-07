<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Product;

use Amasty\MultiInventory\Ui\Component\Listing\Column\AbstractColumn;

/**
 * Class Warehouse
 */
class Warehouse extends AbstractColumn
{
    /**
     * Get data
     *
     * @param array $item
     *
     * @return string
     */
    protected function prepareItem(array $item)
    {
        if ($this->helper->isMultiEnabled()) {
            $content = $this->jsonEncoder->encode($this->getWarehouses($item['entity_id']));
        } else {
            $content = $this->jsonEncoder->encode($this->getInventory($item['entity_id']));
        }

        return $content;
    }
}
