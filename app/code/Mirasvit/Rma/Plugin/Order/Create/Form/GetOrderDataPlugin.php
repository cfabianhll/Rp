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


namespace Mirasvit\Rma\Plugin\Order\Create\Form;

use Magento\Sales\Block\Adminhtml\Order\Create\Form as OrderForm;

/**
 * @package Mirasvit\Rma\Plugin\Order\Create\Form
 */
class GetOrderDataPlugin
{
    /**
     * @param OrderForm    $form
     * @param \callable $proceed
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetOrderDataJson(OrderForm $form, $proceed)
    {
        return str_replace("'", "`", $proceed()); // Magento bug. Fixing quote in address
    }
}