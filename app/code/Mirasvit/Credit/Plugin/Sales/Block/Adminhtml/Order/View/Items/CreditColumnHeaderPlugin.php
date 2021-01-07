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



namespace Mirasvit\Credit\Plugin\Sales\Block\Adminhtml\Order\View\Items;

use Magento\Sales\Block\Adminhtml\Order\View\Items;

class CreditColumnHeaderPlugin
{
    /**
     * @param Items     $itemsRenderer
     * @param \callable $proceed
     *
     * @return array
     */
    public function aroundGetColumns(Items $itemsRenderer, $proceed)
    {
        $result = $proceed();

        $containsCreditProduct = false;
        $items = $itemsRenderer->getItemsCollection();
        foreach ($items as $item) {
            if ($item->getProductType() == \Mirasvit\Credit\Model\Product\Type::TYPE_CREDITPOINTS) {
                $containsCreditProduct = true;
            }
        }
        if (!$containsCreditProduct) {
            unset($result['credit']);
        }

        return $result;
    }
}