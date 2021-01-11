<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Appadmin\Plugin\Order\Create;

/**
 * Plugin customize block order totals
 *
 * Use for deducting Magestore discount when displaying total discount
 */
class Totals extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals
{
    /**
     * Around render total
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\Create\Totals $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $total
     * @param string|null $area
     * @param int $colspan
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRenderTotal(
        \Magento\Sales\Block\Adminhtml\Order\Create\Totals $subject,
        \Closure $proceed,
        $total,
        $area = null,
        $colspan = 1
    ) {
        if ($total->getCode() == 'discount') {
            if ($total->getValue() != 0) {
                $total->setValue($total->getValue() + $this->getQuote()->getMagestoreDiscount());
                if ($total->getValue() != 0) {
                    return $proceed($total, $area, $colspan);
                }
            }
            return '';
        } else {
            return $proceed($total, $area, $colspan);
        }
    }
}
