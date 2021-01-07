<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\InventorySalesApi;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Magento\Catalog\Model\ResourceModel\Product;

/**
 * Class IsProductSalableInterfacePlugin
 */
class IsProductSalableInterfacePlugin
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var Product
     */
    private $productResource;

    public function __construct(
        System $system,
        Data $dataHelper,
        Product $productResource
    ) {
        $this->system = $system;
        $this->dataHelper = $dataHelper;
        $this->productResource = $productResource;
    }

    /**
     * @param $subject
     * @param \Closure $procceed
     * @param string $sku
     * @param int $stockId
     *
     * @return bool|mixed
     */
    public function aroundExecute($subject, \Closure $procceed, $sku, $stockId)
    {
        if (!$this->system->isMultiEnabled() || !$this->system->isLockOnStore()) {
            return $procceed($sku, $stockId);
        }
        $productId = $this->productResource->getIdBySku($sku);
        $stock = $this->dataHelper->getStock($productId);

        if (!$stock->getId()) {
            return $procceed($sku, $stockId);
        }

        return (bool)$stock->getStockStatus();
    }
}
