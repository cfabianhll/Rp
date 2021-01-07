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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Credit\Model\Balance;

class ShareBalance implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionHash()
    {
        return [
            Balance::SHARE_BALANCE_GLOBAL   => __('Global'),
            Balance::SHARE_BALANCE_CURRENCY => __('Per Currency'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toOptionHash() as $value => $label) {
            $result[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        return $result;
    }
}
