<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Dispatch
 */
class Dispatch implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Order Shipment')],
            ['value' => 1, 'label' => __('Order Creation')],
            ['value' => 2, 'label' => __('Invoice Creation')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            0 => __('Order Shipment'),
            1 => __('Order Creation'),
            2 => __('Invoice Creation')
        ];
    }
}
