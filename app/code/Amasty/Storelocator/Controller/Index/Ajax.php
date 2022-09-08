<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */

namespace Amasty\Storelocator\Controller\Index;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magestore\Storepickup\Helper\Image;
use Magestore\Storepickup\Model\ResourceModel\Image\CollectionFactory;
use Magestore\Storepickup\Model\ResourceModel\Store\Collection;
use Zend_Json;
use Magento\Catalog\Api\ProductRepositoryInterface;


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

    /**
     * @var CollectionFactory
     */
    protected $_imageCollectionFactory;

    /**
     * Registry object.
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Image
     */
    protected $_imageHelper;

    /**
     * @var Data
     */
    protected $_jsonHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        ObjectManagerInterface $objectManager,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magestore\Storepickup\Model\ResourceModel\Store\CollectionFactory $storeCollectionFactory,
        CollectionFactory $imageCollectionFactory,
        Registry $coreRegistry,
        Image $imageHelper,
        Data $jsonHelper,
        \Magento\Catalog\Helper\Data $helper,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->jsonResultFactory = $jsonResultFactory;
        $this->objectManager = $objectManager;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->_storeCollectionFactory = $storeCollectionFactory;
        $this->_imageCollectionFactory = $imageCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_imageHelper = $imageHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->productRepository = $productRepository;

    }

    public function execute()
    {
        /** @var Json $result */
        $product_id = $this->getRequest()->getParam('product_id');
        $result = $this->jsonResultFactory->create();
        $this->_view->loadLayout();
        if ($product_id) {
            $_product = $this->productRepository->getById($product_id);
        }
//        $block = $this->_view->getLayout()->getBlock('amlocator_ajax');
        //        $productId = $block->getProductId();

        $status = $this->getStockStatus($product_id);
        
        $data = ['amlocator_block' => json_decode($this->getListStoreJson()),
                'location_img'=> json_decode($this->getImageJsonData()),
                'stock_status' => $status
        ];
//        echo "<pre>";
//        print_r($this->getListStore());
//        die("LL");
        $result->setData($data);
        return $result;
    }

    /**
     * get images json data of store.
     *
     * @return string
     */
    public function getImageJsonData()
    {
//        $store = $this->_coreRegistry->registry('storepickup_store');
        /** @var Collection $collection */
        $collection = $this->_storeCollectionFactory->create();
        $storepickup_id = $collection->addFieldToFilter('status', '1')
            ->addFieldToSelect(
                [
                    'storepickup_id'
                ]
            )->getData();

        $imageCollection = $this->_imageCollectionFactory->create()
            ->addFieldToFilter('pickup_id', $storepickup_id);

        $imageArray = [];
        foreach ($imageCollection as $image) {
            $imageData = [
                'file' => $image->getPath(),
                'url' => $this->_imageHelper->getMediaUrlImage($image->getPath()),
                'image_id' => $image->getId(),
            ];

            $imageArray[] = $imageData;
        }

        return $this->_jsonHelper->jsonEncode($imageArray);
    }

    public function getStockStatus($product_id)
    {
        $stock = [];
        if ($product_id) {
            $_product = $this->productRepository->getById($product_id);
        }
        $sku = $_product->getSku();
        $getSourceItemsBySku = $this->objectManager->get('Magento\InventoryApi\Api\GetSourceItemsBySkuInterface');
        $sourceRepository = $this->objectManager->get('Magento\InventoryApi\Api\SourceRepositoryInterface');
        $sourceItems = $getSourceItemsBySku->execute($sku);
        foreach ($sourceItems as $sourceItem) {
            $source = $sourceRepository->get($sourceItem->getSourceCode());
            $qty[] = (float)$sourceItem->getQuantity();
            $status[] = $sourceItem->getStatus();
            $code[] = $sourceItem->getData('source_code');
//            echo "<pre>";
//            print_r($sourceItem->getData());
            $stock = ['qty'=>$qty, 'status'=>$status, 'code' => $code ];//[] = $qty;
        }
    //    echo "<pre>";
    //    print_r($stock);
    //    die("LL");
        return $stock;
    }

    /**
     * Get list store json Magestore
     *
     * @return string
     */
    public function getListStoreJson()
    {
        /** @var Collection $collection */
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

        return Zend_Json::encode($collection->getData());
    }

    /**
     * Filter store by website
     *
     * @param Collection $collection
     * @return Collection
     */
    public function filterStoreByWebsite($collection)
    {
        if (1) {
            $objectManager = ObjectManager::getInstance();
            $getStockIdForCurrentWebsite = $objectManager->get(
                GetStockIdForCurrentWebsite::class
            );
            $stockId = $getStockIdForCurrentWebsite->execute();
            $searchCriteriaBuilder = $objectManager->create(SearchCriteriaBuilder::class);
            $searchCriteria = $searchCriteriaBuilder->addFilter('stock_id', $stockId)->create();
            $getStockSourceLinks = $objectManager->get(GetStockSourceLinksInterface::class);
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
}

