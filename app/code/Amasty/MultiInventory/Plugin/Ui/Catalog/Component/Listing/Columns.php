<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Ui\Catalog\Component\Listing;

/**
 * Class Columns
 */
class Columns
{
    /**
     * @param \Magento\Catalog\Ui\Component\Listing\Columns $columns
     */
    public function beforePrepare(\Magento\Catalog\Ui\Component\Listing\Columns $columns)
    {
        $components = $columns->getChildComponents();
        if (isset($components['qty'])) {

            $component = $components['qty'];
            $config = $component->getData('config');

            $config['label'] = __('Total Qty');
            $config['add_field'] = true;
            $component->setData('config', $config);
        }
    }
}
