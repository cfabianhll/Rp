<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\CatalogInventory\Api\Data;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\Module\Manager;

/**
 * Class StockItemInterfacePlugin
 */
class StockItemInterfacePlugin
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
     * @var Manager
     */
    private $moduleManager;

    /**
     * StockItemInterfacePlugin constructor.
     *
     * @param System $system
     * @param Data $dataHelper
     * @param Manager $moduleManager
     */
    public function __construct(
        System $system,
        Data $dataHelper,
        Manager $moduleManager
    ) {
        $this->system = $system;
        $this->dataHelper = $dataHelper;
        $this->moduleManager = $moduleManager;
    }

    /**
     * If amasty pre order is enabled we need to disable product stock manage
     * due to check backorder status from warehouse item
     *
     * @param StockItemInterface $subject
     * @param int $result
     *
     * @return mixed
     */
    public function afterGetManageStock(StockItemInterface $subject, $result)
    {
        if (!$this->system->isMultiEnabled() || !$this->moduleManager->isEnabled('Amasty_Preorder')) {
            return $result;
        }
        $stockWh = $this->dataHelper->getStock($subject->getProductId());

        if ($stockWh->getId()) {
            $result = $stockWh->getBackorders() != \Amasty\Preorder\Helper\Data::BACKORDERS_PREORDER_OPTION;
        }

        return (int)$result;
    }
}
