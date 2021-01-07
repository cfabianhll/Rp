<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Helper\Distance as HelperData;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\Collection;
use Amasty\MultiInventory\Model\Warehouse\StoreFactory;
use Magento\CatalogInventory\Model\Stock;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\App\Cache;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface\Proxy;

/**
 * Class Dispatch
 *
 * @codingStandardsIgnoreFile
 */
class Dispatch extends DataObject
{
    const STATUS_RESPONSE = 200;

    const DIRECTION_ORDER = 'order';

    const DIRECTION_QUOTE = 'quote';

    const DIRECTION_STORE = 'store';

    const EARTH_RADIUS = 6371; // in km

    private static $CALCULATED_NEAREST = [];

    /**
     * @var array
     */
    protected $warehouses = [];

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $quoteItem;

    /**
     * @var Item
     */
    private $orderItem;

    /**
     * @var ResourceModel\Warehouse\Store\CollectionFactory
     */
    private $collectionStoreFactory;

    /**
     * @var Warehouse\ItemFactory
     */
    private $stockFactory;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var ResourceModel\Warehouse\CustomerGroup\CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var Curl
     */
    private $clientUrl;

    /**
     * @var System
     */
    private $system;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var ListsInterface
     */
    private $localeLists;

    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var array
     */
    protected $callables;

    /**
     * @var array
     */
    protected $exclude = [];

    /**
     * @var string
     */
    private $direction;

    /**
     * @var ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $stockCollectionFactory;

    /**
     * @var ResourceModel\Warehouse\CollectionFactory
     */
    private $warehouseCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session\Proxy
     */
    private $customerSession;

    /**
     * @var Proxy
     */
    private $storeManager;

    /**
     * @var int
     */
    protected $defaultId;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CustomerCoordinatesFactory
     */
    private $customerCoordinatesFactory;

    /**
     * @var WarehouseRepository
     */
    private $warehouseRepository;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Dispatch constructor.
     *
     * @param ResourceModel\Warehouse\Store\CollectionFactory $collectionStoreFactory
     * @param Warehouse\ItemFactory $stockFactory
     * @param ResourceModel\Warehouse\Item\CollectionFactory $stockCollectionFactory
     * @param StoreFactory $storeFactory
     * @param WarehouseFactory $whFactory
     * @param ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     * @param ResourceModel\Warehouse\CustomerGroup\CollectionFactory $groupCollectionFactory
     * @param System $system
     * @param Curl $clientUrl
     * @param RegionFactory $regionFactory
     * @param ListsInterface $localeLists
     * @param DecoderInterface $jsonDecoder
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param Proxy $storeManager
     * @param Registry $registry
     * @param CustomerCoordinatesFactory $customerCoordinates
     * @param WarehouseRepository $warehouseRepository
     * @param HelperData $helperData
     * @param Cache $cache
     * @param EncoderInterface $jsonEncoder
     * @param ManagerInterface $messageManager
     * @param array $data
     */
    public function __construct(
        ResourceModel\Warehouse\Store\CollectionFactory $collectionStoreFactory,
        Warehouse\ItemFactory $stockFactory,
        ResourceModel\Warehouse\Item\CollectionFactory $stockCollectionFactory,
        Warehouse\StoreFactory $storeFactory,
        WarehouseFactory $whFactory,
        ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        ResourceModel\Warehouse\CustomerGroup\CollectionFactory $groupCollectionFactory,
        System $system,
        Curl $clientUrl,
        RegionFactory $regionFactory,
        ListsInterface $localeLists,
        DecoderInterface $jsonDecoder,
        \Magento\Customer\Model\Session\Proxy $customerSession,
        Proxy $storeManager,
        Registry $registry,
        CustomerCoordinatesFactory $customerCoordinates,
        WarehouseRepository $warehouseRepository,
        HelperData $helperData,
        Cache $cache,
        EncoderInterface $jsonEncoder,
        ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct($data);
        $this->collectionStoreFactory = $collectionStoreFactory;
        $this->stockFactory = $stockFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->storeFactory = $storeFactory;
        $this->warehouseFactory = $whFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->clientUrl = $clientUrl;
        $this->system = $system;
        $this->regionFactory = $regionFactory;
        $this->localeLists = $localeLists;
        $this->jsonDecoder = $jsonDecoder;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->customerCoordinatesFactory = $customerCoordinates;
        $this->warehouseRepository = $warehouseRepository;
        $this->helperData = $helperData;
        $this->cache = $cache;
        $this->jsonEncoder = $jsonEncoder;
        $this->messageManager = $messageManager;
    }

    /**
     * Get Warehouse via Store View
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function searchStoreView()
    {
        $store = $this->registry->registry('amasty_store_id');
        if (null === $store) {
            $store = $this->getStoreId();
        }

        $stores = [$store, 0];

        $collection = $this->storeFactory->create()->getCollection()
            ->addFieldToFilter('store_id', ['in' => $stores]);

        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }

        if ($this->system->isLockOnStore() || $collection->getSize()) {
            $this->setFromCollection($collection);
        }

        return $this;
    }

    /**
     * Get the closest warehouse
     *
     * @return $this
     * @throws \Zend_Json_Exception
     */
    public function searchNearest()
    {
        if (!$this->system->isAddressSuggestionEnabled()) {
            return $this;
        }

        if ($this->system->isUseGoogleForDistance()) {
            $this->calculateByGoogle();
        } else {
            $this->calculateByCoordinates();
        }

        return $this;
    }

    /**
     * Send request to google to check distance
     *
     * @return void
     * @throws \Zend_Json_Exception
     */
    public function calculateByGoogle()
    {
        $shipping = [];
        $shippingAddress = $this->getShippingAddress();

        if ($shippingAddress) {
            $shipping = [
                'country' => $this->localeLists->getCountryTranslation($shippingAddress->getCountryId()),
                'state' => $shippingAddress->getRegion(),
                'city' => $shippingAddress->getCity(),
                'address' => implode(",", $shippingAddress->getStreet()),
                'zip' => $shippingAddress->getPostCode()
            ];
        }

        if ((!isset($shipping['address']) || empty($shipping['address']))
            && (!isset($shipping['zip']) || empty($shipping['zip']))
        ) {
            return;
        }
        $warehousesToShip = $this->getWarehouseByDistance($shipping);

        if ($warehousesToShip) {
            $this->warehouses = [$warehousesToShip];
        }
    }

    /**
     * Get the closest warehouse for customer
     *
     * @param array $shipping
     *
     * @return bool|int|string|null
     * @throws \Zend_Json_Exception
     */
    private function getWarehouseByDistance(array $shipping)
    {
        $originAddresses = [];
        $warehouseId = false;
        $addresses = $this->getWarehouseAddresses();
        // We consider and determine the closest distance via Google Api
        if (empty($addresses)) {
            return $warehouseId;
        }
        $destinationQuery = $this->buildQuery($shipping);
        /** cache data with destination address, warehouses id and address */
        $cacheKey = sha1('amasty_multiinventory' . $destinationQuery . sha1($this->jsonEncoder->encode($addresses)));

        if ($cache = $this->cache->load($cacheKey)) {
            $result = $this->jsonDecoder->decode($cache);

            return key($result);
        }
        $destinations = '&destinations=' . $destinationQuery;

        foreach ($addresses as $address) {
            $originAddresses[] = $this->buildQuery($address);
        }
        $origins = 'origins=' . implode('|', $originAddresses);
        $query = $origins . $destinations . "&key=" . $this->system->getGoogleMapsKey();
        $url = $this->system->getGoogleDistancematrix() . $query;
        $result = $this->getGoogleRequestResult($url, $addresses);

        if (count($result) > 0) {
            asort($result);
            reset($result);
            $this->cache->save($this->jsonEncoder->encode($result), $cacheKey);
            $warehouseId = key($result);
        }

        return $warehouseId;
    }

    /**
     * Get warehouse ids and distance between this warehouse and customer
     *
     * @param $url
     * @param array $addresses
     *
     * @return array[$warehouseId] = $distance
     * @throws \Zend_Json_Exception
     */
    public function getGoogleRequestResult($url, $addresses)
    {
        $result = [];
        $response = $this->sendGoogleRequest($url);

        if (!empty($response)) {
            if ($response['status'] == 'OK') {
                if (isset($response['rows'])) {
                    $distances = $this->prepareDistancesFromResponse($response);
                    $i = 0;

                    foreach ($addresses as $id => $address) {
                        $result[$id] = $distances[$i];
                        ++$i;
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Response from Google is empty.'));
                }
            } elseif ($response['status'] == 'REQUEST_DENIED') {
                $this->messageManager->addErrorMessage(__($response['error_message']));
            } elseif ($response['status'] == 'INVALID_REQUEST') {
                $this->messageManager->addNoticeMessage(__($response['error_message']));
            }
        }

        return $result;
    }

    /**
     * @param string $url
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    protected function sendGoogleRequest($url)
    {
        $this->clientUrl->write(
            \Zend_Http_Client::GET,
            $url,
            '1.1',
            []
        );
        $googleResponse = $this->clientUrl->read();
        $responseBody = \Zend_Http_Response::extractBody($googleResponse);

        return \Zend_Json::decode($responseBody);
    }

    /**
     * @param array $response
     *
     * @return array
     */
    private function prepareDistancesFromResponse($response)
    {
        $distances = [];

        foreach ($response['rows'] as $row) {
            if (isset($row['elements'][0]['status']) && $row['elements'][0]['status'] == 'OK') {
                $distances[] = (int)$row['elements'][0]['distance']['value'];
            } else {
                $distances[] = self::EARTH_RADIUS * pi() * 2 * 1000; // equator length
            }
        }

        return $distances;
    }

    /**
     * Get shipping address to calculate distance
     *
     * @return Address|OrderAddressInterface|null
     */
    public function getShippingAddress()
    {
        $address = null;

        switch ($this->getDirection()) {
            case self::DIRECTION_QUOTE:
                $quote = $this->getQuoteItem()->getQuote();
                $shipQuote = $quote->getShippingAddress();

                if ($shipQuote) {
                    $address = $shipQuote;
                }
                break;
            case self::DIRECTION_ORDER:
                $shipOrder = $this->getOrderItem()->getOrder()->getAddresses();

                foreach ($shipOrder as $addressItem) {
                    if ($addressItem->getAddressType() == 'shipping') {
                        $address = $addressItem;
                    }
                }
        }

        return $address;
    }

    /**
     * Check distance according coordinates
     *
     * @return $this
     * @throws \Zend_Json_Exception
     */
    public function calculateByCoordinates()
    {
        $address = $this->getShippingAddress();

        if (!$address->getPostCode()) {
            return $this;
        }
        $prepearedAddress = $this->helperData->prepareAddressForGoogle($address->getData());
        $cacheKey = $prepearedAddress . implode(',', $this->warehouses);

        if (isset(self::$CALCULATED_NEAREST[$cacheKey])) {
            $this->warehouses = self::$CALCULATED_NEAREST[$cacheKey];
            return $this;
        }
        $customerCoordinates = $this->customerCoordinatesFactory->create();
        $customerCoordinates->load($address->getCustomerAddressId(), CustomerCoordinates::ADDRESS_ID);

        if ($customerCoordinates->getLng() && $customerCoordinates->getLat()) {
            $from = [
                'lat' => $customerCoordinates->getLat(),
                'lng' => $customerCoordinates->getLng()
            ];
        } else {
            $from = $this->helperData->getCoordinatesByAddress($prepearedAddress);
        }

        if (!$from) {
            return $this;
        }
        $whCoordinates = $this->getWarehouseCoordinates();
        $distance = 0;

        foreach ($whCoordinates as $id => $to) {
            $result = $this->calculateDistance($from, $to);

            if ($result < $distance || !$distance) {
                $distance = $result;
                $this->warehouses = [$id];
                self::$CALCULATED_NEAREST[$cacheKey] = $this->warehouses;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getWarehouseAddresses()
    {
        $collection = $this->getWarehouseCollection();
        $addresses = [];

        foreach ($collection->getItems() as $item) {
            if ($item->getCountry()) {
                $addresses[$item->getId()] = [
                    'country' => $this->localeLists->getCountryTranslation($item->getCountry()),
                    'state' => $this->correctState($item->getState(), $item->getCountry()),
                    'city' => $item->getCity(),
                    'address' => $item->getAddress(),
                    'zip' => $item->getZip()
                ];
            }
        }

        return $addresses;
    }

    /**
     * Get warehouse coordinates by distance
     *
     * @return array
     */
    public function getWarehouseCoordinates()
    {
        $coordinates = [];
        $collection = $this->getWarehouseCollection();

        foreach ($collection->getItems() as $item) {
            if (!$item->getLat() || !$item->getLng()) {
                try {
                    $item = $this->warehouseRepository->save($item);
                } catch (LocalizedException $e) {
                    // todo::add logs
                }
            }

            if ($item->getLng() || $item->getLat()) {
                $coordinates[$item->getId()] = [
                    'lat' => $item->getLat(),
                    'lng' => $item->getLng()
                ];
            }
        }

        return $coordinates;
    }

    /**
     * @return ResourceModel\Warehouse\Collection
     */
    public function getWarehouseCollection()
    {
        $collection = $this->warehouseCollectionFactory->create();

        if (!empty($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }

        return $collection;
    }

    /**
     * Calculate distance between 2 coordinates with earth curvature
     *
     * @param array $from
     * @param array $to
     * @return float
     */
    public function calculateDistance($from, $to)
    {
        $pi = pi() / 180;
        $dLat = (($from['lat'] - $to['lat']) * $pi) / 2;
        $dLon = (($from['lng'] - $to['lng']) * $pi) / 2;
        $a = sin($dLat) ** 2 + cos($to['lat'] * $pi) * cos($from['lat'] * $pi) * sin($dLon) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * Get Warehouse via Priority
     * @return $this
     */
    public function searchPriorityWarehouses()
    {
        $collection = $this->warehouseCollectionFactory->create();

        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }
        $collection->getSelect()->order('priority ASC');

        if ($collection->getSize()) {
            $this->warehouses = [];
            $priority = 0;
            $incPriority = 0;

            foreach ($collection as $collect) {
                if (!$collect->getPriority()) {
                    continue;
                }

                if (!$priority) {
                    $this->warehouses[] = $collect->getId();
                    $priority = $collect->getPriority();
                    $incPriority++;
                    continue;
                }

                if ($incPriority == 1 && $priority == $collect->getPriority()) {
                    $this->warehouses[] = $collect->getId();
                } else {
                    $incPriority++;
                }
            }
        }

        return $this;
    }

    /**
     * Get Warehouse via Customer Group
     *
     * @return $this
     */
    public function searchCustomerGroup()
    {
        $collection = $this->groupCollectionFactory->create()
            ->addFieldToFilter('group_id', $this->getCustomerGroupId());

        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }

        if ($collection->getSize()) {
            $this->setFromCollection($collection);
        }

        return $this;
    }

    /**
     * Search warehouse with whole stock for avoid split
     * @since 1.3.0
     */
    public function searchStock()
    {
        $qty = 'qty';

        if ($this->system->getAvailableDecreese()) {
            $qty = 'available_qty';
        }
        $collection = $this->stockCollectionFactory->create()
            ->addFieldToFilter($qty, ['gteq' => $this->getRequestedQty()])
            ->setOrder('available_qty');

        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }

        if ($collection->getSize()) {
            $this->setFromCollection($collection);
        }
    }

    /**
     * Get Warehouse via Stock
     * @return $this
     */
    public function searchProductInStock()
    {
        $collection = $this->stockCollectionFactory->create()
            ->addFieldToFilter('product_id', $this->getProductId())
            ->addFieldToFilter(
                WarehouseItemInterface::STOCK_STATUS,
                Stock::STOCK_IN_STOCK
            )
            ->setOrder('available_qty');

        if (count($this->warehouses)) {
            $collection->addFieldToFilter('warehouse_id', ['in' => $this->warehouses]);
        }

        if ($collection->getSize()) {
            $this->warehouses = [];

            foreach ($collection as $collect) {
                $this->warehouses[] = $collect->getWarehouseId();
                break;
            }
        }

        return $this;
    }

    /**
     * Return Default Warehouse
     *
     * @return $this
     */
    public function getGeneral()
    {
        $productId = $this->getProductId();
        /** @var Collection $collection */
        $collection = $this->stockCollectionFactory->create()
            ->addActiveWarehouseFilter();

        if (!$this->system->isLockOnStore()) {
            $collection->addFieldToFilter(
                WarehouseItemInterface::STOCK_STATUS,
                Stock::STOCK_IN_STOCK
            );
        }
        $excludes = $this->getExludeWarehouses();

        if ($productId) {
            $collection->addFieldToFilter('product_id', $productId);

            if (isset($excludes[$productId]) && count($excludes[$productId])) {
                $excluded = [];

                foreach ($excludes[$productId] as $wh) {
                    if (!in_array($wh, $excluded)) {
                        $excluded[] = $wh;
                    }
                }

                if (!empty($excluded)) {
                    $collection->addFieldToFilter('main_table.warehouse_id', ['nin' => $excluded]);
                }
            }
        }
        $this->setFromCollection($collection);

        return $this;
    }

    /**
     * @return int[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Json_Exception
     * @since 1.3.0 added new criteria "Stock"
     */
    public function searchWh()
    {
        $this->warehouses = [];
        $this->getGeneral();

        if (count($this->warehouses) > 0) {
            $callables = $this->getCallables();

            foreach ($callables as $key => $options) {
                if ($this->checkCount()) {
                    return $this->warehouses;
                }

                if ($options['is_active']) {
                    $this->searchByAlgorithm($key);
                }
            }

            if (count($this->warehouses) > 1) {
                $this->searchProductInStock();
            }
        }
        $this->addDefaultWh();

        return $this->warehouses;
    }

    /**
     * Search wh by algorithm
     *
     * @param string $key
     *
     * @throws \Zend_Json_Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function searchByAlgorithm($key)
    {
        switch ($key) {
            case 'customer_group':
                $this->searchCustomerGroup();
                break;
            case 'nearest':
                $this->searchNearest();
                break;
            case 'priority_warehouses':
                $this->searchPriorityWarehouses();
                break;
            case 'store_view':
                $this->searchStoreView();
                break;
            case 'stock':
                $this->searchStock();
                break;
            default:
                $method = 'search' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key);

                if (is_callable([$this, $method])) {
                    $this->{$method}();
                }
                break;
        }
    }

    /**
     * Add default wh if no other wh found and direction not store
     */
    protected function addDefaultWh()
    {
        if (!count($this->warehouses) && $this->getDirection() !== self::DIRECTION_STORE) {
            $this->warehouses[] = $this->getDefaultWarehouseId();
        }
    }

    /**
     * @return Item|\Magento\Quote\Model\Quote\Item
     */
    public function getObject()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem();
            default:
                return $this->_getData('object');
        }
    }

    /**
     * @return int|null
     */
    private function getProductId()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem()->getProductId();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem()->getProduct()->getId();
            default:
                $data = $this->_getData('object');
                if (is_object($data) && $data->hasData('product_id')) {
                    return $data->getProductId();
                }
                return null;
        }
    }

    /**
     * @return int
     */
    private function getCustomerGroupId()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem()->getOrder()->getCustomerGroupId();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem()->getQuote()->getCustomerGroupId();
            default:
                return $this->customerSession->getCustomerGroupId();
        }
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreId()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
            case self::DIRECTION_QUOTE:
                return $this->getObject()->getStoreId();
            default:
                if (null !== $this->getQuoteItem()) {
                    return $this->getQuoteItem()->getQuote()->getStoreId();
                }
                if (null !== $this->getOrderItem()) {
                    return $this->getOrderItem()->getOrder()->getStoreId();
                }

                return $this->storeManager->getStore()->getId();
        }
    }

    private function getRequestedQty()
    {
        switch ($this->getDirection()) {
            case self::DIRECTION_ORDER:
                return $this->getOrderItem()->getQtyOrdered();
            case self::DIRECTION_QUOTE:
                return $this->getQuoteItem()->getQty();
            default:
                return 1;
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getQuoteItem()
    {
        return $this->quoteItem;
    }

    /**
     * @return Item
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return $this
     */
    public function setQuoteItem($item)
    {
        $this->quoteItem = $item;

        return $this;
    }

    /**
     * @param Item $item
     * @return $this
     */
    public function setOrderItem($item)
    {
        $this->orderItem = $item;

        return $this;
    }

    /**
     * @param string $direction
     * @return $this
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return bool
     */
    protected function checkCount()
    {
        if (count($this->warehouses) == 1 && !$this->system->isLockOnStore()) {
            return true;
        }

        return false;
    }

    /**
     * @param AbstractCollection $collection
     */
    protected function setFromCollection($collection)
    {
        $this->warehouses = [];
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([WarehouseInterface::ID])
            ->group(WarehouseInterface::ID);

        foreach ($collection->getData() as $wh) {
            $this->warehouses[] = $wh[WarehouseInterface::ID];
        }
    }

    /**
     * @return int
     */
    public function getWarehouse()
    {
        if (count($this->warehouses) >= 1) {
            return $this->warehouses[0];
        }

        return $this->getDefaultWarehouseId();
    }

    /**
     * @return int
     */
    public function getDefaultWarehouseId()
    {
        if ($this->defaultId === null) {
            $this->defaultId = $this->warehouseFactory->create()->getDefaultId();
        }

        return $this->defaultId;
    }

    /**
     * @return array
     */
    public function getWarehousesRaw()
    {
        return $this->warehouses;
    }

    /**
     * @param $address
     * @return string
     */
    private function buildQuery($address)
    {
        $query = "";
        $arrayCodes = ['country', 'state', 'city', 'address', 'zip'];

        foreach ($arrayCodes as $code) {
            if (isset($address[$code]) && !empty($address[$code])) {
                if (strlen($query) > 0) {
                    $query .= " ";
                }
                $query .= $address[$code];
            }
        }

        return urlencode($query);
    }

    /**
     * @param array $callables
     * @return $this
     */
    public function setCallables($callables)
    {
        $this->callables = $callables;

        return $this;
    }

    /**
     * @return array
     */
    public function getCallables()
    {
        if ($this->callables === null) {
            $this->setCallables($this->system->getDispatchOrder());
        }

        return $this->callables;
    }

    /**
     * @return array
     */
    public function getExludeWarehouses()
    {
        return $this->exclude;
    }

    /**
     * Add Exclude Warehouse for product
     *
     * @param int $productId
     * @param int $warehouseId
     *
     * @return $this
     */
    public function addExclude($productId, $warehouseId)
    {
        if (!isset($this->exclude[$productId])) {
            $this->exclude[$productId] = [];
        }
        $this->exclude[$productId][] = $warehouseId;

        return $this;
    }

    /**
     * reset array
     *
     * @return $this
     */
    public function resetExclude()
    {
        $this->exclude = [];

        return $this;
    }

    /**
     * @param $state
     * @param $countryId
     * @return string
     */
    private function correctState($state, $countryId)
    {
        if (!empty($state)) {
            if (is_numeric($state) && $countryId) {
                return $this->regionFactory->create()->loadByCode($state, $countryId)->getName();
            }
        }

        return $state;
    }
}
