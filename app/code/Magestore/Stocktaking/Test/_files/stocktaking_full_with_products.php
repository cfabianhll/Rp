<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var  StocktakingRepositoryInterface $stocktakingRepository */
$stocktakingRepository = $objectManager->get(StocktakingRepositoryInterface::class);
/** @var StocktakingItemRepositoryInterface $stocktakingItemRepository */
$stocktakingItemRepository = $objectManager->get(StocktakingItemRepositoryInterface::class);
/** @var DefaultSourceProviderInterface $defaultSourceProvider */
$defaultSourceProvider = $objectManager->get(DefaultSourceProviderInterface::class);

// Create stocktaking
$stocktakingData =[
    StocktakingInterface::SOURCE_CODE => 'source-code-1',
    StocktakingInterface::CODE => 'ST00000002',
    StocktakingInterface::CREATED_AT => '2020-09-10',
    StocktakingInterface::DESCRIPTION => 'Stocktaking',
    StocktakingInterface::STOCKTAKING_TYPE => StocktakingInterface::STOCKTAKING_TYPE_FULL,
    StocktakingInterface::STATUS => StocktakingInterface::STATUS_COUNTING
];

/** @var StocktakingInterface $stocktaking */
$stocktaking = $objectManager->create(StocktakingInterface::class);
$stocktaking->setData($stocktakingData);
$stocktakingRepository->save($stocktaking);

// Add stocktaking items
/** @var ProductInterface $product1 */
$product1 = $productRepository->get('SKU-1');
/** @var ProductInterface $product2 */
$product2 = $productRepository->get('SKU-2');
/** @var ProductInterface $product3 */
$product3 = $productRepository->get('SKU-3');

$stocktakingItems = [
    [
        StocktakingItemInterface::STOCKTAKING_ID => $stocktaking->getId(),
        StocktakingItemInterface::PRODUCT_ID => $product1->getId(),
        StocktakingItemInterface::PRODUCT_NAME => $product1->getName(),
        StocktakingItemInterface::PRODUCT_SKU => $product1->getSku(),
        StocktakingItemInterface::QTY_IN_SOURCE => 5.5,
        StocktakingItemInterface::COUNTED_QTY => 10.5,
        StocktakingItemInterface::DIFFERENCE_REASON => 'Difference reason 1'
    ],
    [
        StocktakingItemInterface::STOCKTAKING_ID => $stocktaking->getId(),
        StocktakingItemInterface::PRODUCT_ID => $product2->getId(),
        StocktakingItemInterface::PRODUCT_NAME => $product2->getName(),
        StocktakingItemInterface::PRODUCT_SKU => $product2->getSku(),
        StocktakingItemInterface::QTY_IN_SOURCE => 5,
        StocktakingItemInterface::COUNTED_QTY => 5,
    ],
    [
        StocktakingItemInterface::STOCKTAKING_ID => $stocktaking->getId(),
        StocktakingItemInterface::PRODUCT_ID => $product3->getId(),
        StocktakingItemInterface::PRODUCT_NAME => $product3->getName(),
        StocktakingItemInterface::PRODUCT_SKU => $product3->getSku(),
        StocktakingItemInterface::QTY_IN_SOURCE => 6,
        StocktakingItemInterface::COUNTED_QTY => 0
    ],
];

$stocktakingItemRepository->setStocktakingItems($stocktaking->getId(), $stocktakingItems);
