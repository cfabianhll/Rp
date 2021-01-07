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



namespace Mirasvit\Rma\Model\Config\Source\Rule;

use Mirasvit\Rma\Api\Config\RuleConfigInterface as Config;

class Event implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Config::RULE_EVENT_RMA_CREATED => __('New RMA has been created'),
            Config::RULE_EVENT_RMA_UPDATED => __('RMA has been changed'),
            Config::RULE_EVENT_NEW_CUSTOMER_REPLY => __('New reply from customer'),
            Config::RULE_EVENT_NEW_STAFF_REPLY => __('New reply from staff'),
        ];
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
