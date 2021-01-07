<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Traits;

use Amasty\MultiInventory\Model\Config\Source\Backorders;

/**
 * @codingStandardsIgnoreFile
 */
trait ConfigOptions
{
    /**
     * Convert data from tree format to flat format
     *
     * @return array
     */
    public function toFlatArray()
    {
        $options = [];
        foreach ($this->toOptionArray() as $item) {
            if (isset($item['value']) && isset($item['label'])) {
                $options[$item['value']] = $item['label'];
            }
        }

        return $options;
    }

    /**
     * Add option "Use config" to flat array
     * @return array
     */
    public function getGridOptions()
    {
        return [Backorders::USE_CONFIG_OPTION_VALUE => __('Use Config Settings')] + $this->toFlatArray();
    }
}
