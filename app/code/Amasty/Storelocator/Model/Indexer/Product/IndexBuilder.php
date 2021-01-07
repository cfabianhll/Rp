<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\Indexer\Product;

use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\Indexer\AbstractIndexBuilder;
use Amasty\Storelocator\Model\Indexer\Location\LocationIndexer;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Amasty\Storelocator\Model\ResourceModel\LocationProductIndex;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class IndexBuilder extends AbstractIndexBuilder
{
    /**
     * @var Data
     */
    private $storelocatorHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var
     */
    private $locationIndexer;

    /**
     * @var LocationProductIndex
     */
    protected $locationProduct;

    public function __construct(
        LocationCollectionFactory $locationCollectionFactory,
        LoggerInterface $logger,
        Data $storelocatorHelper,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        LocationIndexer $locationIndexer,
        LocationProductIndex $locationProduct,
        $batchSize = 1000
    ) {
        $this->storelocatorHelper = $storelocatorHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->locationIndexer = $locationIndexer;
        $this->locationProduct = $locationProduct;
        parent::__construct($locationCollectionFactory, $logger, $productCollectionFactory, $batchSize);
    }

    /**
     * @inheritDoc
     */
    protected function doReindexByIds($ids)
    {
        $productCollection = $this->getAllProducts();
        $productCollection->addFieldToFilter('id', ['in' => $ids]);

        foreach ($productCollection as $product) {
            $this->updateLocationProductsIndex($product);
        }
    }

    /**
     * @inheritDoc
     */
    protected function doReindexFull()
    {
        $this->locationIndexer->executeFull();
    }

    /**
     * @param int $productId
     *
     * @return void
     * @throws LocalizedException
     */
    public function reindexByProductId($productId)
    {
        $this->reindexByProductIds([$productId]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByProductIds(array $ids)
    {
        try {
            $this->doReindexByProductIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     * @throws \Exception
     */
    protected function doReindexByProductIds($ids)
    {
        $this->locationProduct->deleteByIds(null, $ids);

        foreach ($this->getAllLocations() as $location) {
            foreach ($ids as $id) {
                $product = $this->productRepository->getById($id);
                $productIds = $location->isValidForLocation($product);
                if ($productIds) {
                    $this->updateLocation($location, $productIds);
                }
            }
        }
    }

    /**
     * Collect product matches for location
     *
     * @param Product $product
     * @return $this
     * @throws \Exception
     */
    protected function updateLocationProductsIndex(Product $product)
    {
        $productId = $product->getId();
        $this->locationProduct->deleteByIds(null, $productId);

        if (!$product->getStatus()) {
            return $this;
        }

        $locationCollection = $this->getAllLocations();
        $rows = [];
        $count = 0;
        foreach ($product->getStoreIds() as $storeId) {
            foreach ($locationCollection->getItems() as $location) {
                $product->setStoreId($storeId);
                if ($location->getProductConditions()->validate($product)) {
                    $rows[] = [
                        LocationProductIndex::LOCATION_ID => $location->getId(),
                        LocationProductIndex::PRODUCT_ID => $productId,
                        LocationProductIndex::STORE_ID => $storeId
                    ];

                    if (++$count == $this->batchSize) {
                        $this->locationProduct->insertData($rows);
                        $rows = [];
                        $count = 0;
                    }
                }
            }
        }

        if (!empty($rows)) {
            $this->locationProduct->insertData($rows);
        }

        return $this;
    }

    /**
     * Reindex location when product save
     *
     * @param Location $location
     * @param int[]    $productIds
     *
     * @return IndexBuilder
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateLocation(Location $location, $productIds)
    {
        $locationId = $location->getId();
        $this->locationProduct->deleteByIds($locationId, array_keys($productIds));

        $rows = [];
        $count = 0;
        foreach ($productIds as $productId => $stores) {
            foreach ($stores as $storeId => $value) {
                $rows[] = [
                    LocationProductIndex::LOCATION_ID => $locationId,
                    LocationProductIndex::PRODUCT_ID => $productId,
                    LocationProductIndex::STORE_ID => $storeId
                ];

                if (++$count == $this->batchSize) {
                    $this->locationProduct->insertData($rows);
                    $rows = [];
                    $count = 0;
                }
            }
        }

        if (!empty($rows)) {
            $this->locationProduct->insertData($rows);
        }

        return $this;
    }
}
