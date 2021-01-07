<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\WarehouseAbstractInterface;
use Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface;
use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Amasty\MultiInventory\Api\Data\WarehouseItemInterface;
use Amasty\MultiInventory\Api\Data\WarehouseShippingInterface;
use Amasty\MultiInventory\Api\Data\WarehouseStoreInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Logger\Logger;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup\Collection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\CustomerGroup;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\Warehouse\Store;
use Exception;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Warehouse
 */
class Warehouse extends AbstractExtensibleModel implements WarehouseInterface
{
    /**
     * @var ResourceModel\Warehouse\CustomerGroup\CollectionFactory
     */
    private $collectionGroupFactory;

    /**
     * @var ResourceModel\Warehouse\Store\CollectionFactory
     */
    private $collectionStoresFactory;

    /**
     * @var ResourceModel\Warehouse\Item\CollectionFactory
     */
    private $collectionItemsFactory;

    /**
     * @var ResourceModel\Warehouse\Shipping\CollectionFactory
     */
    private $collectionShippingsFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var System
     */
    private $system;

    /**
     * @var Warehouse\ItemFactory
     */
    private $itemWarehouseFactory;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $whRepository;

    /**
     * @var Processor
     */
    private $processor;

    protected $_eventPrefix = 'amasty_warehouse';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Warehouse::class);
    }

    /**
     * Warehouse constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param ResourceModel\Warehouse\CustomerGroup\CollectionFactory $collectionGroupFactory
     * @param ResourceModel\Warehouse\Store\CollectionFactory $collectionStoresFactory
     * @param ResourceModel\Warehouse\Item\CollectionFactory $collectionItemsFactory
     * @param ResourceModel\Warehouse\Shipping\CollectionFactory $collectionShippingsFactory
     * @param Warehouse\ItemFactory $itemWarehouseFactory
     * @param WarehouseItemRepositoryInterface $itemRepository
     * @param WarehouseRepositoryInterface $whRepository
     * @param Logger $logger
     * @param System $system
     * @param Processor $processor
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        CollectionFactory $collectionGroupFactory,
        ResourceModel\Warehouse\Store\CollectionFactory $collectionStoresFactory,
        ResourceModel\Warehouse\Item\CollectionFactory $collectionItemsFactory,
        ResourceModel\Warehouse\Shipping\CollectionFactory $collectionShippingsFactory,
        Warehouse\ItemFactory $itemWarehouseFactory,
        WarehouseItemRepositoryInterface $itemRepository,
        WarehouseRepositoryInterface $whRepository,
        Logger $logger,
        System $system,
        Processor $processor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->collectionGroupFactory = $collectionGroupFactory;
        $this->collectionStoresFactory = $collectionStoresFactory;
        $this->collectionItemsFactory = $collectionItemsFactory;
        $this->collectionShippingsFactory = $collectionShippingsFactory;
        $this->logger = $logger;
        $this->system = $system;
        $this->itemWarehouseFactory = $itemWarehouseFactory;
        $this->itemRepository = $itemRepository;
        $this->whRepository = $whRepository;
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        if ($this->getData(self::CUSTOMER_GROUPS) == null) {
            $this->setData(
                self::CUSTOMER_GROUPS,
                $this->getGroupsCollection()->getItems()
            );
        }

        return $this->getData(self::CUSTOMER_GROUPS);
    }

    /**
     * @return array
     */
    public function getGroupIds()
    {
        return $this->getResource()->getGroupIds($this->getId());
    }

    /**
     * @return Collection
     */
    public function getGroupsCollection()
    {
        $collection = $this->collectionGroupFactory->create()
            ->addFieldToFilter(WarehouseAbstractInterface::WAREHOUSE_ID, $this->getId());

        if ($this->getId()) {
            /** @var Warehouse\CustomerGroup $item */
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }

        return $collection;
    }

    /**
     * @return WarehouseStoreInterface[]
     */
    public function getStores()
    {
        if ($this->getData(self::STORES) == null) {
            $this->setData(
                self::STORES,
                $this->getStoresCollection()->getItems()
            );
        }

        return $this->getData(self::STORES);
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return $this->getResource()->getStoreIds($this->getId());
    }

    /**
     * @return ResourceModel\Warehouse\Store\Collection
     */
    public function getStoresCollection()
    {
        $collection = $this->collectionStoresFactory->create()
            ->addFieldToFilter(WarehouseAbstractInterface::WAREHOUSE_ID, $this->getId());

        if ($this->getId()) {
            /** @var Store $item */
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }
        return $collection;
    }

    /**
     * @return WarehouseShippingInterface[]
     */
    public function getShippings()
    {
        if ($this->getData(self::SHIPPINGS) == null) {
            $this->setData(
                self::SHIPPINGS,
                $this->getShippingsCollection()->getItems()
            );
        }
        return $this->getData(self::SHIPPINGS);
    }

    /**
     * @return array
     */
    public function getShippingsCodes()
    {
        return $this->getResource()->getShippingsCodes($this->getId());
    }

    /**
     * @return ResourceModel\Warehouse\Shipping\Collection
     */
    public function getShippingsCollection()
    {
        $collection = $this->collectionShippingsFactory->create()
            ->addFieldToFilter(WarehouseAbstractInterface::WAREHOUSE_ID, $this->getId());

        if ($this->getId()) {
            /** @var \Amasty\MultiInventory\Model\Warehouse\Shipping $item */
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }

        return $collection;
    }

    /**
     * @return WarehouseItemInterface[]
     */
    public function getItems()
    {
        if ($this->getData(self::ITEMS) === null) {
            $this->setData(
                self::ITEMS,
                $this->getItemsCollection()->getItems()
            );
        }

        return $this->getData(self::ITEMS);
    }

    /**
     * Items delete from warehouses
     *
     * @return WarehouseItemInterface[]
     */
    public function getRemoveItems()
    {
        return $this->getData(self::REMOVE_ITEMS);
    }

    /**
     * @return array
     */
    public function getItemIds()
    {
        return $this->getResource()->getItemIds($this->getId());
    }

    /**
     * @return ResourceModel\Warehouse\Item\Collection
     */
    public function getItemsCollection()
    {
        $collection = $this->collectionItemsFactory->create()
            ->addFieldToFilter('warehouse_id', $this->getId());

        if ($this->getId()) {
            /** @var Item $item */
            foreach ($collection as $item) {
                $item->setWarehouse($this);
            }
        }

        return $collection;
    }

    /**
     * @return array
     */
    public function getProductsToGrid()
    {
        return $this->getResource()->getItemsToGrid($this);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->getResource()->getItems($this);
    }

    /**
     * Count products
     *
     * @return int
     */
    public function getTotalSku()
    {
        return $this->getResource()->getTotalSku($this);
    }

    /**
     * Sum Qty for Warehouses
     *
     * @param string $field
     *
     * @return int
     */
    public function getTotalQty($field = 'qty')
    {
        return $this->getResource()->getTotalQty($this, $field);
    }

    /**
     * General Sum from All Warehouses
     *
     * @param int $productId
     *
     * @return int
     */
    private function getAlltotalQty($productId)
    {
        return $this->getResource()->getAllTotalQty($productId, $this->getDefaultId());
    }

    /**
     * @return int
     */
    public function getDefaultId()
    {
        return $this->getResource()->getDefaultId(Stock::DEFAULT_STOCK_ID);
    }

    /**
     * Get is not actives Warehouses
     *
     * @return array
     */
    public function getWhNotActive()
    {
        return $this->getResource()->getWhNotActive(Stock::DEFAULT_STOCK_ID);
    }

    /**
     * Get codes Warehouses
     *
     * @return array
     */
    public function getWhCodes()
    {
        return $this->getResource()->getWhCodes(Stock::DEFAULT_STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerGroups($groups)
    {
        return $this->setData(self::CUSTOMER_GROUPS, $groups);
    }

    /**
     * @param array $stores
     *
     * @return Warehouse
     */
    public function setStores($stores)
    {
        return $this->setData(self::STORES, $stores);
    }

    /**
     * @param array $shippings
     *
     * @return Warehouse
     */
    public function setShippings($shippings)
    {
        return $this->setData(self::SHIPPINGS, $shippings);
    }

    /**
     * @param array $items
     *
     * @return Warehouse
     */
    public function setItems($items)
    {
        return $this->setData(self::ITEMS, $items);
    }

    /**
     * @param array $items
     *
     * @return Warehouse
     */
    public function setRemoveItems($items)
    {
        return $this->setData(self::REMOVE_ITEMS, $items);
    }

    /**
     * @param Warehouse\CustomerGroup $group
     *
     * @return Warehouse
     */
    public function addGroupCustomer(CustomerGroup $group)
    {
        $group->setWarehouse($this);
        if (!$group->getId()) {
            $this->setCustomerGroups(array_merge($this->getCustomerGroups(), [$group]));
        }

        return $this;
    }

    /**
     * @param Warehouse\Store $store
     *
     * @return Warehouse
     */
    public function addStore(Store $store)
    {
        $store->setWarehouse($this);
        if (!$store->getId()) {
            $this->setStores(array_merge($this->getStores(), [$store]));
        }

        return $this;
    }

    /**
     * @param Warehouse\Shipping $shipping
     *
     * @return Warehouse
     */
    public function addShippings(\Amasty\MultiInventory\Model\Warehouse\Shipping $shipping)
    {
        $shipping->setWarehouse($this);
        if (!$shipping->getId()) {
            $this->setShippings(array_merge($this->getShippings(), [$shipping]));
        }

        return $this;
    }

    /**
     * Add product for Warehouses
     *
     * @param Warehouse\Item $item
     *
     * @return Warehouse
     * @throws Exception
     */
    public function addItem(Item $item)
    {
        $collection = $this->collectionItemsFactory->create()
            ->addFieldToFilter(self::ID, $this->getId())
            ->addFieldToFilter(WarehouseItemInterface::PRODUCT_ID, $item->getProductId());
        // if product in warehouse
        if ($collection->getSize()) {
            $changeItem = $collection->getFirstItem();
            $oldQty = $changeItem->getQty();
            $changeItem->setData(array_merge($changeItem->getData(), $item->getData()))
                ->recalcAvailable()
                ->save();

            if ($this->system->isEnableLog()) {
                $this->logger->infoWh(
                    $changeItem->getProduct()->getSku(),
                    $changeItem->getProductId(),
                    $changeItem->getWarehouse()->getTitle(),
                    $changeItem->getWarehouse()->getCode(),
                    $oldQty,
                    $changeItem->getQty()
                );
            }
        } else {
            // if product is not in warehouse
            if (!$item->getId()) {
                $item->setWarehouse($this);
                $item->recalcAvailable();
                $item->save();

                if ($this->system->isEnableLog()) {
                    $this->logger->infoWh(
                        $item->getProduct()->getSku(),
                        $item->getProductId(),
                        $item->getWarehouse()->getTitle(),
                        $item->getWarehouse()->getCode(),
                        0,
                        $item->getQty(),
                        "null",
                        "null",
                        "true"
                    );
                }
            }
        }

        return $this;
    }

    /**
     * If on page Warehoses delete products
     *
     * @param Warehouse\Item $item
     *
     * @return Warehouse
     * @throws NoSuchEntityException
     */
    public function addRemoveItem(Item $item)
    {
        $items = $this->getRemoveItems();
        if (!$items) {
            $this->setRemoveItems([$item]);
        } else {
            $this->setItems(array_merge($items, [$item]));
        }
        $item->setWarehouse($this);

        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $item->getProduct()->getSku(),
                $item->getProductId(),
                $item->getWarehouse()->getTitle(),
                $item->getWarehouse()->getCode(),
                $item->getQty(),
                0,
                "null",
                "null",
                "true"
            );
        }

        return $this;
    }

    /**
     * @param int|null $groupId
     *
     * @return Warehouse
     */
    public function deleteGroup($groupId = null)
    {
        $groups = $this->collectionGroupFactory->create()
            ->addFieldToFilter(self::ID, $this->getId());

        if ($groupId) {
            $groups->addFieldToFilter(WarehouseCustomerGroupInterface::GROUP_ID, $groupId);
        }

        foreach ($groups as $item) {
            $item->delete();
        }
        $this->setData(
            self::CUSTOMER_GROUPS,
            $this->getGroupsCollection()->getItems()
        );

        return $this;
    }

    /**
     * @param int|null $storeId
     *
     * @return Warehouse
     */
    public function deleteStore($storeId = null)
    {
        $stores = $this->collectionStoresFactory->create()
            ->addFieldToFilter(self::ID, $this->getId());

        if ($storeId) {
            $stores = $stores->addFieldToFilter(WarehouseStoreInterface::STORE_ID, $storeId);
        }

        foreach ($stores as $item) {
            $item->delete();
        }
        $this->setData(
            self::STORES,
            $this->getStoresCollection()->getItems()
        );

        return $this;
    }

    /**
     * @param string|null $shippingCode
     * @return Warehouse
     */
    public function deleteShipping($shippingCode = null)
    {
        $shippings = $this->collectionShippingsFactory->create()
            ->addFieldToFilter(self::ID, $this->getId());

        if ($shippingCode) {
            $shippings = $shippings->addFieldToFilter(WarehouseShippingInterface::SHIPPING_METHOD, $shippingCode);
        }

        foreach ($shippings as $item) {
            $item->delete();
        }
        $this->setData(
            self::SHIPPINGS,
            $this->getShippingsCollection()->getItems()
        );

        return $this;
    }

    /**
     * @param int|null $itemId
     *
     * @return Warehouse
     */
    public function deleteItems($itemId = null)
    {
        $items = $this->collectionItemsFactory->create()
            ->addFieldToFilter(self::ID, $this->getId());

        if ($itemId) {
            $items->addFieldToFilter(WarehouseItemInterface::PRODUCT_ID, $itemId);
        }

        foreach ($items as $item) {
            $item->delete();
        }

        return $this;
    }

    /**
     * Recalculate for Total Stock from all warehouses
     *
     * Recalculate for Inventory
     */
    public function recalcInventory()
    {
        $whItems = $this->getItems();

        if (!$whItems) {
            $whItems = [];
        }
        $whRemItems = $this->getRemoveItems();

        if (!$whRemItems) {
            $whRemItems = [];
        }
        $items = array_merge($whItems, $whRemItems);
        $productIds = [];

        foreach ($items as $item) {
            $productId = $item->getProductId();
            $productIds[] = $productId;

            if ($this->system->isEnableLog()) {
                $totalItem = $this->createDefaultStock($item->getProductId());
                $oldQty = $totalItem->getQty();
                $newQty = $this->getAlltotalQty($productId);

                $this->logger->infoWh(
                    $totalItem->getProduct()->getSku(),
                    $productId,
                    $totalItem->getWarehouse()->getTitle(),
                    $totalItem->getWarehouse()->getCode(),
                    $oldQty,
                    $newQty
                );
            }
        }

        if (count($productIds)) {
            $this->processor->reindexList($productIds);
        }
    }

    /**
     * @param int $productId
     *
     * @return DataObject|mixed|null
     * @throws LocalizedException
     */
    private function createDefaultStock($productId)
    {
        $totalItem = null;
        $totalStock = $this->collectionItemsFactory->create()
            ->addFieldToFilter(self::ID, $this->getDefaultId())
            ->addFieldToFilter(WarehouseItemInterface::PRODUCT_ID, $productId);

        if ($totalStock->getSize()) {
            $totalItem = $totalStock->getFirstItem();
        } else {
            $object = $this->itemWarehouseFactory->create();
            $warehouse = $this->whRepository->getById($this->getDefaultId());
            $object->setWarehouse($warehouse);
            $object->setProductId($productId);
            $object->setQty(0);
            $this->itemRepository->save($object);
            $totalItem = $object;
        }

        return $totalItem;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_getData(WarehouseInterface::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->setData(WarehouseInterface::TITLE, $title);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->_getData(WarehouseInterface::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $this->setData(WarehouseInterface::CODE, $code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry()
    {
        return $this->_getData(WarehouseInterface::COUNTRY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountry($country)
    {
        $this->setData(WarehouseInterface::COUNTRY, $country);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->_getData(WarehouseInterface::STATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->setData(WarehouseInterface::STATE, $state);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->_getData(WarehouseInterface::CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        $this->setData(WarehouseInterface::CITY, $city);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->_getData(WarehouseInterface::ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress($address)
    {
        $this->setData(WarehouseInterface::ADDRESS, $address);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getZip()
    {
        return $this->_getData(WarehouseInterface::ZIP);
    }

    /**
     * {@inheritdoc}
     */
    public function setZip($zip)
    {
        $this->setData(WarehouseInterface::ZIP, $zip);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPhone()
    {
        return $this->_getData(WarehouseInterface::PHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPhone($phone)
    {
        $this->setData(WarehouseInterface::PHONE, $phone);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->_getData(WarehouseInterface::EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->setData(WarehouseInterface::EMAIL, $email);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->_getData(WarehouseInterface::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->setData(WarehouseInterface::DESCRIPTION, $description);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isManage()
    {
        return $this->_getData(WarehouseInterface::MANAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setManage($manage)
    {
        $this->setData(WarehouseInterface::MANAGE, $manage);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return $this->_getData(WarehouseInterface::PRIORITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority($priority)
    {
        $this->setData(WarehouseInterface::PRIORITY, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isGeneral()
    {
        return $this->_getData(WarehouseInterface::IS_GENERAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGeneral($isGeneral)
    {
        $this->setData(WarehouseInterface::IS_GENERAL, $isGeneral);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderEmailNotification()
    {
        return $this->_getData(WarehouseInterface::ORDER_EMAIL_NOTIFICATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderEmailNotification($orderEmailNotification)
    {
        $this->setData(WarehouseInterface::ORDER_EMAIL_NOTIFICATION, $orderEmailNotification);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLowStockNotification()
    {
        return $this->_getData(WarehouseInterface::LOW_STOCK_NOTIFICATION);
    }

    /**
     * {@inheritdoc}
     */
    public function setLowStockNotification($lowStockNotification)
    {
        $this->setData(WarehouseInterface::LOW_STOCK_NOTIFICATION, $lowStockNotification);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStockId()
    {
        return $this->_getData(WarehouseInterface::STOCK_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStockId($stockId)
    {
        $this->setData(WarehouseInterface::STOCK_ID, $stockId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateTime()
    {
        return $this->_getData(WarehouseInterface::CREATE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreateTime($createTime)
    {
        $this->setData(WarehouseInterface::CREATE_TIME, $createTime);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdateTime()
    {
        return $this->_getData(WarehouseInterface::UPDATE_TIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdateTime($updateTime)
    {
        $this->setData(WarehouseInterface::UPDATE_TIME, $updateTime);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isShipping()
    {
        return $this->_getData(WarehouseInterface::IS_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsShipping($isShipping)
    {
        $this->setData(WarehouseInterface::IS_SHIPPING, $isShipping);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackorders()
    {
        return $this->_getData(WarehouseInterface::BACKORDERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBackorders($backorders)
    {
        $this->setData(WarehouseInterface::BACKORDERS, $backorders);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLng()
    {
        return $this->_getData(WarehouseInterface::LNG);
    }

    /**
     * {@inheritdoc}
     */
    public function setLng($lng)
    {
        $this->setData(WarehouseInterface::LNG, $lng);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLat()
    {
        return $this->_getData(WarehouseInterface::LAT);
    }

    /**
     * {@inheritdoc}
     */
    public function setLat($lat)
    {
        $this->setData(WarehouseInterface::LAT, $lat);

        return $this;
    }
}
