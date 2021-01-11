<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Plugin\Catalog\Product;

use Magento\Framework\App\ObjectManager;
use Magestore\Webpos\Helper\Product\CustomSale;
use Magestore\Webpos\Model\Service\Catalog\IsHideCustomSaleFlag;

/**
 * Class Type
 *
 * Used to hide option custom sale
 */
class Type extends \Magento\Catalog\Model\Product\Type
{
    /**
     * After get option array
     *
     * @param \Magento\Catalog\Model\Product\Type $subject
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptionArray(\Magento\Catalog\Model\Product\Type $subject, $result)
    {
        $objectManager = ObjectManager::getInstance();
        $hideCustomeSale = $objectManager->get(IsHideCustomSaleFlag::class);
        $isHideCustomSaleFlag = $hideCustomeSale->getHideCustomSaleFlag();
        if ($isHideCustomSaleFlag) {
            if (isset($result[CustomSale::TYPE])) {
                unset($result[CustomSale::TYPE]);
            }
            $hideCustomeSale->setHideCustomSaleFlag(0);
        }
        return $result;
    }
}
