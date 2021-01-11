<?php

namespace Magestore\Webpos\Model\Indexer\Preference\InventoryElasticsearch\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\InventoryElasticsearch\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider\StockedProductFilterByInventoryStock as CoreStockedProductFilterByInventoryStock; // phpcs:ignore

/**
 * Disable plugin before when reindexing POS data
 */
class StockedProductFilterByInventoryStock
{
    /**
     * Filter out stock options for configurable product.
     *
     * @param CoreStockedProductFilterByInventoryStock $stockedProductFilterByInventoryStock
     * @param mixed $result
     * @param DataProvider $dataProvider
     * @param array $indexData
     * @param array $productData
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBeforePrepareProductIndex(
        CoreStockedProductFilterByInventoryStock $stockedProductFilterByInventoryStock,
        $result,
        DataProvider $dataProvider,
        $indexData,
        $productData,
        $storeId
    ) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Registry $registry */
        $registry = $om->get(\Magento\Framework\Registry::class);
        if ($registry->registry('webpos_productsearch_fulltext')) {
            return [
                $indexData,
                $productData,
                $storeId
            ];
        }
        return $result;
    }
}
