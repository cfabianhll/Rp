<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */

namespace Amasty\Storelocator\Controller\Index;

use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class Ajax extends Action
{
    /** @var JsonFactory */
    protected $jsonResultFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var \Magestore\Storepickup\Model\ResourceModel\Store\CollectionFactory
     *
     */
    protected $_storeCollectionFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magestore\Storepickup\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->objectManager = $objectManager;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;

    }

    public function execute()
    {
        /** @var Json $result */
        $result = $this->jsonResultFactory->create();
        $this->_view->loadLayout();
        $block = $this->_view->getLayout()->getBlock('amlocator_ajax');
        //        $productId = $block->getProductId();
        $status = $this->getStockStatus($block);
        $data = ['amlocator_block' => json_decode($this->getListStoreJson()),
                'location_img'=> $this->getLocationImageArray($block),
                'stock_status' => $status];
//        echo "<pre>";
//        print_r($this->getListStore());
//        die("LL");
        $result->setData($data);
        return $result;
    }

    public function getLocationImageArray($block)
    {
        $locations = $block->getLocationCollection();
        $location_img = [];
        foreach ($locations as $item) {
            $locationId = $item->getData('id');
            $location_img[] = $block->getLocationImage($locationId);
        }
        return $location_img;
    }

    public function getStockStatus($block)
    {
        $stock = [];
        $product = $block->getProduct();
        $sku = $product->getSku();
        $getSourceItemsBySku = $this->objectManager->get('Magento\InventoryApi\Api\GetSourceItemsBySkuInterface');
        $sourceRepository = $this->objectManager->get('Magento\InventoryApi\Api\SourceRepositoryInterface');
        $sourceItems = $getSourceItemsBySku->execute($sku);
        foreach ($sourceItems as $sourceItem) {
            $source = $sourceRepository->get($sourceItem->getSourceCode());
            $qty[$sourceItem->getData('source_code')] = (float)$sourceItem->getQuantity();
            $status[] = $sourceItem->getStatus();
            $code[] = $sourceItem->getData('source_code');
//            echo "<pre>";
//            print_r($sourceItem->getData());
            $stock = ['qty'=>$qty, 'status'=>$status, 'code' => $code];//[] = $qty;
        }
//        echo "<pre>";
//        print_r($stock);
//        die("LL");
        return $stock;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAllOptions()
    {
//        if (!$this->_options) {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        $sources = $this->sourceRepository->getList($searchCriteria)->getItems();
        foreach ($sources as $source) {
            $this->_options[] = [
                    'value' => $source->getData(),
                    'label' => $source->getCountryId()
                ];
        }
//        }
        return $this->_options;
    }

    /**
     * Get list store json Magestore
     *
     * @return string
     */
    public function getListStoreJson()
    {
        /** @var \Magestore\Storepickup\Model\ResourceModel\Store\Collection $collection */
        $collection = $this->_storeCollectionFactory->create();
        $collection->addFieldToFilter('status', '1')
            ->addFieldToSelect(
                [
                    'storepickup_id',
                    'store_name',
                    'address',
                    'phone',
                    'latitude',
                    'longitude',
                    'city',
                    'state',
                    'zipcode',
                    'country_id',
                    'fax'
                ]
            );

//        $this->filterStoreByWebsite($collection);

        return \Zend_Json::encode($collection->getData());
    }

    /**
     * Filter store by website
     *
     * @param \Magestore\Storepickup\Model\ResourceModel\Store\Collection $collection
     * @return \Magestore\Storepickup\Model\ResourceModel\Store\Collection
     */
    public function filterStoreByWebsite($collection)
    {
        if (1) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $getStockIdForCurrentWebsite = $objectManager->get(
                \Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite::class
            );
            $stockId = $getStockIdForCurrentWebsite->execute();
            $searchCriteriaBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
            $searchCriteria = $searchCriteriaBuilder->addFilter('stock_id', $stockId)->create();
            $getStockSourceLinks = $objectManager->get(\Magento\InventoryApi\Api\GetStockSourceLinksInterface::class);
            $searchResult = $getStockSourceLinks->execute($searchCriteria);
            if ($searchResult->getTotalCount() > 0) {
                $sourceCodes = [];
                foreach ($searchResult->getItems() as $link) {
                    $sourceCodes[] = $link->getSourceCode();
                }
                $collection->addFieldToFilter('source_code', ['in' => $sourceCodes]);
            } else {
                $collection->addFieldToFilter('storepickup_id', 0);
            }

        }
        return $collection;
    }

    /**
     * Get list store
     *
     * @return mixed
     */
    public function getListStore()
    {
        $collection = $this->_storeCollectionFactory->create();
        $collection->addFieldToFilter('status', '1')
            ->addFieldToSelect(
                [
                    'storepickup_id',
                    'store_name',
                    'address',
                    'phone',
                    'latitude',
                    'longitude',
                    ''
                ]
            );

//        $this->filterStoreByWebsite($collection);

        return $collection->getData();
    }
}

