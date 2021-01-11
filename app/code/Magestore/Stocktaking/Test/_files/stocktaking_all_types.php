<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var  StocktakingRepositoryInterface $stocktakingRepository */
$stocktakingRepository = $objectManager->get(StocktakingRepositoryInterface::class);

$stocktakingDataList = [
    [
        StocktakingInterface::SOURCE_CODE => 'default',
        StocktakingInterface::CODE => 'ST00000001',
        StocktakingInterface::CREATED_AT => '2020-09-09',
        StocktakingInterface::DESCRIPTION => 'Stocktaking',
        StocktakingInterface::STOCKTAKING_TYPE => StocktakingInterface::STOCKTAKING_TYPE_FULL,
        StocktakingInterface::STATUS => StocktakingInterface::STATUS_PREPARING
    ],
    [
        StocktakingInterface::SOURCE_CODE => 'default',
        StocktakingInterface::CODE => 'ST00000002',
        StocktakingInterface::CREATED_AT => '2020-09-10',
        StocktakingInterface::DESCRIPTION => 'Stocktaking',
        StocktakingInterface::STOCKTAKING_TYPE => StocktakingInterface::STOCKTAKING_TYPE_PARTIAL,
        StocktakingInterface::STATUS => StocktakingInterface::STATUS_COUNTING
    ]
];

foreach ($stocktakingDataList as $stocktakingData) {
    /**
     * @var StocktakingInterface $stocktaking
     */
    $stocktaking = $objectManager->create(StocktakingInterface::class);
    $stocktaking->setData($stocktakingData);
    try {
        $stocktakingRepository->save($stocktaking);
    } catch (\Exception $exception) {
        return;
    }
}
