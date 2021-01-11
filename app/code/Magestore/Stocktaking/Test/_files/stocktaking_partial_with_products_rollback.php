<?php
/**
 * Rollback for stocktaking_partial_with_products.php fixture.
 *
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\Collection as StocktakingArchiveCollection;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$stocktakingList = $objectManager->create(Collection::class);
foreach ($stocktakingList as $stocktaking) {
    $stocktaking->delete();
}

$stocktakingArchiveList = $objectManager->create(StocktakingArchiveCollection::class);
foreach ($stocktakingArchiveList as $stocktakingArchive) {
    $stocktakingArchive->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
