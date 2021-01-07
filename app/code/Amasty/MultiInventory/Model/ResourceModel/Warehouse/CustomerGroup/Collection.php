<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup as CustomerGroupResource;
use Amasty\MultiInventory\Model\Warehouse\CustomerGroup;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CustomerGroup::class, CustomerGroupResource::class);
    }
}
