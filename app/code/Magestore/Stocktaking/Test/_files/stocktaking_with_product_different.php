<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var  StocktakingRepositoryInterface $stocktakingRepository */
$stocktakingRepository = $objectManager->get(StocktakingRepositoryInterface::class);
$stocktakingItemRepository = $objectManager->get(StocktakingItemRepositoryInterface::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$stocktakingDataList = [
    [
        StocktakingInterface::SOURCE_CODE => 'source-code-1',
        StocktakingInterface::CODE => 'ST00000001',
        StocktakingInterface::CREATED_AT => '2020-09-09',
        StocktakingInterface::DESCRIPTION => 'Stocktaking with different product',
        StocktakingInterface::STOCKTAKING_TYPE => StocktakingInterface::STOCKTAKING_TYPE_PARTIAL,
        StocktakingInterface::STATUS => StocktakingInterface::STATUS_VERIFYING
    ],
    [
        StocktakingInterface::SOURCE_CODE => 'source-code-1',
        StocktakingInterface::CODE => 'ST00000002',
        StocktakingInterface::CREATED_AT => '2020-09-09',
        StocktakingInterface::DESCRIPTION => 'Stocktaking with different product',
        StocktakingInterface::STOCKTAKING_TYPE => StocktakingInterface::STOCKTAKING_TYPE_FULL,
        StocktakingInterface::STATUS => StocktakingInterface::STATUS_VERIFYING
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

    if ($stocktaking->getId()) {
        $stocktakingId = $stocktaking->getId();
        $productSku2 = $productRepository->get('SKU-2');
        $productSku3 = $productRepository->get('SKU-3');
        $stocktakingItemList = [
            [
                StocktakingItemInterface::STOCKTAKING_ID => $stocktakingId,
                StocktakingItemInterface::PRODUCT_ID => $productSku2->getId(),
                StocktakingItemInterface::PRODUCT_NAME => $productSku2->getName(),
                StocktakingItemInterface::PRODUCT_SKU => $productSku2->getSku(),
                StocktakingItemInterface::QTY_IN_SOURCE => 5,
                StocktakingItemInterface::COUNTED_QTY => 5,
                StocktakingItemInterface::DIFFERENCE_REASON => null,
            ],
            [
                StocktakingItemInterface::STOCKTAKING_ID => $stocktakingId,
                StocktakingItemInterface::PRODUCT_ID => $productSku3->getId(),
                StocktakingItemInterface::PRODUCT_NAME => $productSku3->getName(),
                StocktakingItemInterface::PRODUCT_SKU => $productSku3->getSku(),
                StocktakingItemInterface::QTY_IN_SOURCE => 6,
                StocktakingItemInterface::COUNTED_QTY => 7,
                StocktakingItemInterface::DIFFERENCE_REASON => 'Mistake',
            ]
        ];
        foreach ($stocktakingItemList as $stocktakingItemData) {
            $stocktakingItem = $objectManager->create(StocktakingItemInterface::class);
            $stocktakingItem->setData($stocktakingItemData);
            try {
                $stocktakingItemRepository->save($stocktakingItem);
            } catch (\Exception $exception) {
                return;
            }
        }
    }
}
