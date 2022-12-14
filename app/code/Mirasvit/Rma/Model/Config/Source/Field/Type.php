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



namespace Mirasvit\Rma\Model\Config\Source\Field;

use Mirasvit\Rma\Api\Config\FieldConfigInterface as Config;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Config::FIELD_TYPE_TEXT => __('Text'),
            Config::FIELD_TYPE_TEXTAREA => __('Multi-line text'),
            Config::FIELD_TYPE_DATE => __('Date'),
            Config::FIELD_TYPE_CHECKBOX => __('Checkbox'),
            Config::FIELD_TYPE_SELECT => __('Drop-down list'),
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
