<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Indexer\Product;

use Amasty\Storelocator\Model\Indexer\AbstractIndexer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\CacheContext;

/**
 * Class ProductLocatorIndexer for execute
 */
class ProductLocatorIndexer extends AbstractIndexer
{
    /**
     * @var CacheContext
     */
    protected $cacheContext;

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        $this->indexBuilder->reindexByProductIds(array_unique($ids));
        $this->cacheContext->registerEntities(Product::CACHE_TAG, $ids);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($id)
    {
        $this->indexBuilder->reindexByProductId($id);
    }
}
