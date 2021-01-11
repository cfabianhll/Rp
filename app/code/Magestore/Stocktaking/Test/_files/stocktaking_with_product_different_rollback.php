<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$stocktakingList = $objectManager->create(Collection::class)
    ->addFieldToFilter(StocktakingInterface::DESCRIPTION, 'Stocktaking with different product');
foreach ($stocktakingList as $stocktaking) {
    $stocktaking->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
