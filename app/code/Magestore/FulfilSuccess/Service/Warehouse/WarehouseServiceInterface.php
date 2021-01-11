<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\FulfilSuccess\Service\Warehouse;

/**
 * Fulfil - Warehouse service interface
 */
interface WarehouseServiceInterface
{
    const MAX_AVAILABLE_QTY = 999999;

    /**
     * Get list warehouse to pick items
     *
     * @param array $productIds
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $showOutStock
     * @return array
     */
    public function getWarehousesToPick($productIds, $order, $showOutStock = false);
}
