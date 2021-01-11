<?php

namespace Magestore\Webpos\Plugin\Indexer;

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magestore\Webpos\Model\Indexer\Product\Processor;
use Magestore\Webpos\Model\ResourceModel\Catalog\Product;

/**
 * Class Category used for plugin category
 */
class Category
{
    /**
     * indexer processor
     *
     * @var Processor
     */
    protected $_indexerProcessor;

    /**
     * indexer processor
     *
     * @var Product
     */
    protected $resourceProduct;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param Processor $indexerProcessor
     * @param Product $resourceProduct
     * @param DateTime $date
     * @param DateTime\DateTime $dateTime
     */
    public function __construct(
        Processor $indexerProcessor,
        Product $resourceProduct,
        DateTime $date,
        DateTime\DateTime $dateTime
    ) {
        $this->_indexerProcessor = $indexerProcessor;
        $this->resourceProduct = $resourceProduct;
        $this->date = $date;
        $this->dateTime = $dateTime;
    }

    /**
     * Function afterSave used for plugin reindex when save category
     *
     * @param \Magento\Catalog\Model\Category $subject
     * @param \Magento\Catalog\Model\Category $result
     * @return \Magento\Catalog\Model\Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\Category $subject,
        \Magento\Catalog\Model\Category $result
    ) {
        /** @var \Magento\Catalog\Model\Category $result */
        $productIds = $result->getChangedProductIds();
        if (!empty($productIds)) {
            // Update updated_at field to make sync product in offline mode when change product in category
            $updatedAt = $this->date->formatDate($this->dateTime->gmtTimestamp());
            $productTable = $this->resourceProduct->getTable('catalog_product_entity');
            $this->resourceProduct->updateUpdatedTimeOfProducts($productTable, $updatedAt, $productIds);

            // reindex then update by scheduled mode
            if ($this->_indexerProcessor->isIndexerScheduled()) {
                $this->_indexerProcessor->markIndexerAsInvalid();
            } else {
                // reindex when update on save mode
                $this->_indexerProcessor->reindexList($productIds);
            }
        }
        return $result;
    }

    /**
     * Function afterDelete used for plugin reindex when delete category
     *
     * @param \Magento\Catalog\Model\Category $subject
     * @param \Magento\Catalog\Model\Category $result
     * @return \Magento\Catalog\Model\Category
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Model\Category $subject,
        \Magento\Catalog\Model\Category $result
    ) {
        $this->_indexerProcessor->markIndexerAsInvalid();
        return $result;
    }
}
