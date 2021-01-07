<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Ui\Component\Listing\Column\Import;

use Amasty\MultiInventory\Ui\Component\Listing\Column\AbstractColumn;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @throws NoSuchEntityException
     */
    protected function prepareItem(array $item)
    {
        return $this->repository->getById($item['warehouse_id'])->getTitle();
    }
}
