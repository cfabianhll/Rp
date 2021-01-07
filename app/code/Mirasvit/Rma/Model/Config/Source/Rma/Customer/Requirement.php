<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Rma\Model\Config\Source\Rma\Customer;

use Mirasvit\Rma\Api\Config\RmaRequirementConfigInterface as Config;

class Requirement implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        $options = [
            Config::RMA_CUSTOMER_REQUIRES_REASON     => __('Reason'),
            Config::RMA_CUSTOMER_REQUIRES_CONDITION  => __('Condition'),
            Config::RMA_CUSTOMER_REQUIRES_RESOLUTION => __('Resolution'),
        ];

        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $k => $v) {
            $result[] = ['value' => $k, 'label' => $v];
        }

        return $result;
    }

    /************************/
}
