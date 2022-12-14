<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\BarcodeSuccess\Ui\DataProvider\Product\Form;

use Magestore\BarcodeSuccess\Ui\DataProvider\Barcode\DataProvider as ParentBarcodeDataProvider;

/**
 * Class BarcodeDataProvider
 *
 * Used for barcode data provider
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BarcodeDataProvider extends ParentBarcodeDataProvider
{
    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * BarcodeDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\Api\Search\ReportingInterface $reporting
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magestore\BarcodeSuccess\Helper\Data $helper
     * @param \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator
     * @param \Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Framework\Api\Search\ReportingInterface $reporting,
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magestore\BarcodeSuccess\Helper\Data $helper,
        \Magestore\BarcodeSuccess\Model\Locator\LocatorInterface $locator,
        \Magestore\BarcodeSuccess\Model\ResourceModel\Barcode\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $urlBuilder,
            $helper,
            $locator,
            $collectionFactory,
            $productFactory,
            $imageHelper,
            $stockRegistry,
            $meta,
            $data
        );
        $this->filterGroupBuilder = $filterGroupBuilder;
        $productId = $this->request->getParam('product_id', false);
        $savedProductId = $this->locator->get('current_product_id_to_print');
        if ($productId || $savedProductId) {
            if ($productId) {
                $this->locator->add('current_product_id_to_print', $productId);
            }
            $id = ($productId) ? $productId : $savedProductId;
            $this->collection->addFieldToFilter('product_id', $id);
        }
    }

    /**
     * Get Search Criteria
     *
     * @return \Magento\Framework\Api\Search\SearchCriteria|null
     */
    public function getSearchCriteria()
    {
        $searchCriteria = parent::getSearchCriteria();
        $productId = $this->request->getParam('product_id', false);
        if ($productId) {
            $filter = $this->filterBuilder
                ->setField('product_id')
                ->setValue($productId)
                ->setConditionType('eq')->create();
            $filterGroup = $this->filterGroupBuilder->setFilters([$filter])->create();
            $searchCriteria->setFilterGroups([$filterGroup]);
        }
        return $searchCriteria;
    }
}
