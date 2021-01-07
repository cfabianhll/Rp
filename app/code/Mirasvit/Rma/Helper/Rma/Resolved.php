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


namespace Mirasvit\Rma\Helper\Rma;

class Resolved extends \Magento\Framework\App\Helper\AbstractHelper implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @param string|bool $emptyOption
     * @return array
     */
    public function toAdminUserOptionArray($emptyOption = false)
    {
        $result = [];
        if ($emptyOption) {
            $result[0] = __('-- Please Select --');
        }
        $result[] = ['value' => 1, 'label' => __('Mark as unresolved')];
        $result[] = ['value' => 2, 'label' => __('Mark as resolved')];

        return $result;
    }

    /**
     * @param string|bool $emptyOption
     * @return array
     */
    public function getAdminUserOptionArray($emptyOption = false)
    {
        $result = [];
        if ($emptyOption) {
            $result[0] = __('-- Please Select --');
        }
        $result[] = ['value' => 1, 'label' => __('Mark as unresolved')];
        $result[] = ['value' => 2, 'label' => __('Mark as resolved')];

        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->toAdminUserOptionArray(false);
    }

}