<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Import\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Identifier
 */
class Identifier implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('SKU'),
                'value' => 0,
            ],
            [
                'label' => __('ID'),
                'value' => 1,
            ]
        ];

        return $options;
    }
}
