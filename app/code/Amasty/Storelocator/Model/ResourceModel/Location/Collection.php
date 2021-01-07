<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel\Location;

use Amasty\Storelocator\Model\ResourceModel\LocationProductIndex;
use Magento\Framework\DB\Select;
use Magento\Store\Model\Store;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Quote address model object
     *
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $address;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $httpRequest;

    protected $allRules;

    /**
     * @var \Amasty\Storelocator\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    /**
     * @var \Amasty\Geoip\Model\Geolocation
     */
    private $geolocation;

    /**
     * @var \Amasty\Storelocator\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProductIndex
     */
    private $locationProduct;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scope,
        \Magento\Quote\Model\Quote\Address $address,
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Geoip\Model\Geolocation $geolocation,
        \Amasty\Storelocator\Model\ConfigProvider $configProvider,
        LocationProductIndex $locationProduct,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->coreRegistry = $registry;
        $this->address = $address;
        $this->scopeConfig = $scope;
        $this->httpRequest = $httpRequest;
        $this->dataHelper = $dataHelper;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->_setIdFieldName('id');
        $this->serializer = $serializer;
        $this->geolocation = $geolocation;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->configProvider = $configProvider;
        $this->locationProduct = $locationProduct;
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Storelocator\Model\Location::class,
            \Amasty\Storelocator\Model\ResourceModel\Location::class
        );
    }

    /**
     * Apply filters to locations collection
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyDefaultFilters()
    {
        $store = $this->storeManager->getStore(true)->getId();
        $attributesFromRequest = [];
        $productId = (int)$this->request->getParam('product');
        if (!$productId && $this->coreRegistry->registry('current_product')) {
            $productId = $this->coreRegistry->registry('current_product')->getId();
        }
        $categoryId = (int)$this->request->getParam('category');

        $select = $this->getSelect();
        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addFilterByStores([Store::DEFAULT_STORE_ID, $store]);
        }

        $select->where('main_table.status = 1');
        $this->addDistance($select);

        $params = $this->request->getParams();
        if (isset($params['attributes'])) {
            $attributesFromRequest = $this->prepareRequestParams($params['attributes']);
        }
        $this->applyAttributeFilters($attributesFromRequest);

        if ($productId) {
            $this->filterLocationsByProduct($productId, $store);
        } elseif ($categoryId) {
            $this->filterLocationsByCategory($categoryId, $store);
        }
    }

    /**
     * Preparing params from request
     *
     * @param array $attributesData
     *
     * @return array $params
     */
    public function prepareRequestParams($attributesData)
    {
        $params = [];

        foreach ($attributesData as $value) {
            if (!empty($value['value']) || $value['value'] != '') {
                $params[(int)$value['name']][] = (int)$value['value'];
            }
        }

        return $params;
    }

    public function load($printQuery = false, $logQuery = false)
    {
        parent::load($printQuery, $logQuery);

        return $this;
    }

    /**
     * Added distance in select
     *
     * @param Select $select
     *
     * @return Select $select
     */
    public function addDistance($select)
    {
        $lat = (float)$this->request->getPost('lat');
        $lng = (float)$this->request->getPost('lng');
        $sortByDistance = $this->configProvider->getAutomaticLocate()
            || (bool)$this->request->getPost('sortByDistance');
        $ip = $this->httpRequest->getClientIp();

        if ($this->scopeConfig->isSetFlag('amlocator/geoip/use')
            && (!$lat)
        ) {
            $geodata = $this->geolocation->locate($ip);
            $lat = $geodata->getLatitude();
            $lng = $geodata->getLongitude();
        }

        $radius = (float)$this->request->getPost('radius');

        if ($lat && $lng && ($sortByDistance || $radius)) {
            if ($radius) {
                $select->having('distance < ' . $radius);
            }

            if ($sortByDistance) {
                $select->order("distance");
            }

            $select->columns(
                [
                    'distance' => 'SQRT(POW(69.1 * (main_table.lat - ' . $lat . '), 2) + '
                    . 'POW(69.1 * (' . $lng . ' - main_table.lng) * COS(main_table.lat / 57.3), 2))'
                ]
            );
        } else {
            $select->order('main_table.position ASC');
        }

        return $select;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select $countSelect
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(Select::COLUMNS);
        $columns = array_merge($select->getPart(Select::COLUMNS), $this->getSelect()->getPart(Select::COLUMNS));
        $select->setPart(Select::COLUMNS, $columns);
        $countSelect = $this->getConnection()->select()
            ->from($select)
            ->reset(Select::COLUMNS)
            ->columns(new \Zend_Db_Expr(("COUNT(*)")));

        return $countSelect;
    }

    /**
     * Apply filters to locations collection
     *
     * @param array $params
     * @return $this
     */
    public function applyAttributeFilters($params)
    {
        foreach ($params as $attributeId => $value) {
            $attributeId = (int)$attributeId;
            $this->addConditionsToSelect($attributeId, $value);
        }
        $this->getSelect()->group('main_table.id');

        return $this;
    }

    /**
     * Add conditions
     *
     * @param int $attributeId
     * @param int|array $value
     */
    public function addConditionsToSelect($attributeId, $value)
    {
        $attributeTableAlias = 'store_attribute_' . $attributeId;
        $fromPart = $this->getSelect()->getPart('from');
        if (isset($fromPart[$attributeTableAlias])) {
            return;
        }
        $this->getSelect()
            ->joinLeft(
                [$attributeTableAlias => $this->getTable('amasty_amlocator_store_attribute')],
                "main_table.id = $attributeTableAlias.store_id",
                [
                    $attributeTableAlias . 'value'        => $attributeTableAlias . '.value',
                    $attributeTableAlias . 'attribute_id' => $attributeTableAlias . '.attribute_id'
                ]
            );
        if (is_array($value)) {
            $orWhere = [];
            foreach ($value as $optionId) {
                if (!empty($optionId) || $optionId == '0') {
                    $orWhere[] = "($attributeTableAlias .attribute_id IN ($attributeId)"
                        . " AND FIND_IN_SET(($optionId), $attributeTableAlias.value))";
                }
            }
            if ($orWhere) {
                $this->getSelect()->where(implode(' OR ', $orWhere));
            }
        }
    }

    /**
     * Prepare params for filter
     *
     * @param array $params
     * @return array $result
     */
    public function prepareParamsForFilter($params)
    {
        $result = [];

        if (isset($params['attributes'])) {
            //@codingStandardsIgnoreStart
            parse_str($params['attributes'], $attributes);
            //@codingStandardsIgnoreEnd

            if (!empty($attributes['attribute_id']) && !empty($attributes['option'])) {
                foreach ($attributes['attribute_id'] as $attributeId) {
                    if (isset($attributes['option'][$attributeId]) && $attributes['option'][$attributeId] != '') {
                        $result[(int)$attributeId] = (int)$attributes['option'][$attributeId];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param array $storeIds
     * @return Select
     */
    public function addFilterByStores($storeIds)
    {
        $where = [];
        foreach ($storeIds as $storeId) {
            $where[] = 'FIND_IN_SET("' . (int)$storeId . '", `stores`)';
        }

        $where = implode(' OR ', $where);

        return $this->getSelect()->where($where);
    }

    /**
     * Get locations for product
     *
     * @param int|string|array $productIds
     * @param int|string|null|array $storeIds
     */
    public function filterLocationsByProduct($productIds, $storeIds = Store::DEFAULT_STORE_ID)
    {
        $fields = $this->locationProduct->getLocationsByProduct($productIds, [Store::DEFAULT_STORE_ID, $storeIds]);
        $ids = [];

        foreach ($fields as $field) {
            $ids[] = $field[LocationProductIndex::LOCATION_ID];
        }

        $this->addFieldToFilter('main_table.id', ['in' => $ids]);
    }

    /**
     * Get locations for category
     *
     * @param int|string|array $categoryIds
     * @param int|string|null|array $storeIds
     */
    public function filterLocationsByCategory($categoryIds, $storeIds = Store::DEFAULT_STORE_ID)
    {
        $fields = $this->locationProduct->getLocationsByCategory($categoryIds, $storeIds);
        $ids = [];

        foreach ($fields as $field) {
            $ids[] = $field[LocationProductIndex::LOCATION_ID];
        }

        $this->addFieldToFilter('main_table.id', ['in' => $ids]);
    }

    /**
     * Get location data
     *
     * @return array $locationsArray
     */
    public function getLocationData()
    {
        $locationsArray = [];

        $this->joinScheduleTable();

        foreach ($this->getItems() as $location) {
            /** @var \Amasty\Storelocator\Model\Location $location */
            $location['marker_url'] = $location->getMarkerMediaUrl();
            $location['popup_html'] = $location->getPopupHtml();

            /** @var \Amasty\Storelocator\Model\ResourceModel\Location $locationResource */
            $locationResource = $location->getResource();
            $location = $locationResource->setAttributesData($location)->getData();
            $location['schedule_array'] = $this->serializer->unserialize($location['schedule_string']);
            $locationsArray[] = $location;
        }

        return $locationsArray;
    }

    /**
     * Join schedule table
     *
     * @return $this
     */
    public function joinScheduleTable()
    {
        $fromPart = $this->getSelect()->getPart(Select::FROM);
        if (isset($fromPart['schedule_table'])) {
            return $this;
        }
        $this->getSelect()->joinLeft(
            ['schedule_table' => $this->getTable('amasty_amlocator_schedule')],
            'main_table.schedule = schedule_table.id',
            ['schedule_string' => 'schedule_table.schedule']
        );

        return $this;
    }
}
