<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\Collection;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$stocktakingArchiveList = $objectManager->create(Collection::class);
foreach ($stocktakingArchiveList as $stocktaking) {
    $stocktaking->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
