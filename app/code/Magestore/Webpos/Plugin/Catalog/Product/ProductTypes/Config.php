<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Plugin\Catalog\Product\ProductTypes;

use Magento\Framework\App\ObjectManager;
use Magestore\Webpos\Helper\Product\CustomSale;
use Magestore\Webpos\Model\Service\Catalog\IsHideCustomSaleFlag;

/**
 * Class Config
 *
 * Used to hide option custom sale
 */
class Config extends \Magento\Catalog\Model\ProductTypes\Config
{
    /**
     * After get all
     *
     * @param \Magento\Catalog\Model\ProductTypes\Config $subject
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAll(
        \Magento\Catalog\Model\ProductTypes\Config $subject,
        $result
    ) {
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
