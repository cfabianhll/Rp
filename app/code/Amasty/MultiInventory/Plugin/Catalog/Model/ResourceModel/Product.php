<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Catalog\Model\ResourceModel;

use Magento\Framework\Registry;

/**
 * Class Product
 */
class Product
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * Product constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Add registry flag to disable availability recalculation, when user change stock status manually
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product $object
     * @param \Magento\Catalog\Model\Product $product
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Product $object,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->registry->register('am_dont_recalc_availability', true, true);
    }
}
