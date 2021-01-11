<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive as StocktakingArchiveResource;

$objectManager = Bootstrap::getObjectManager();

/* @var StocktakingArchiveResource $stocktakingResource */
$stocktakingResource = $objectManager->get(StocktakingArchiveResource::class);

$stocktakingArchiveDataList = [
    [
        StocktakingArchiveInterface::SOURCE_CODE => 'default',
        StocktakingArchiveInterface::CODE => 'ST00000001',
        StocktakingArchiveInterface::CREATED_AT => '2020-09-09',
        StocktakingArchiveInterface::DESCRIPTION => 'Stocktaking',
        StocktakingArchiveInterface::STOCKTAKING_TYPE => StocktakingArchiveInterface::STOCKTAKING_TYPE_FULL,
        StocktakingArchiveInterface::STATUS => StocktakingArchiveInterface::STATUS_CANCELED
    ],
    [
        StocktakingArchiveInterface::SOURCE_CODE => 'default',
        StocktakingArchiveInterface::CODE => 'ST00000002',
        StocktakingArchiveInterface::CREATED_AT => '2020-09-10',
        StocktakingArchiveInterface::DESCRIPTION => 'Stocktaking',
        StocktakingArchiveInterface::STOCKTAKING_TYPE => StocktakingArchiveInterface::STOCKTAKING_TYPE_PARTIAL,
        StocktakingArchiveInterface::STATUS => StocktakingArchiveInterface::STATUS_COMPLETED
    ]
];

foreach ($stocktakingArchiveDataList as $stocktakingArchiveData) {
    /**
     * @var StocktakingArchiveInterface $stocktakingArchive
     */
    $stocktakingArchive = $objectManager->create(StocktakingArchiveInterface::class);
    $stocktakingArchive->setData($stocktakingArchiveData);
    try {
        $stocktakingResource->save($stocktakingArchive);
    } catch (\Exception $exception) {
        return;
    }
}
