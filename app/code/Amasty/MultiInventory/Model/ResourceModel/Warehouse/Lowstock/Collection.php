<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse\Lowstock;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item;
use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Reports\Model\Event\TypeFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Collection
{
    private $inventoryItemJoined = false;

    /**
     * @var string
     */
    private $inventoryItemTableAlias = 'amasty_lowstock_inventory_item';

    /**
     * @var Item
     */
    private $itemResource;

    /**
     * @var System
     */
    private $helper;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Config $eavConfig
     * @param ResourceConnection $resource
     * @param EntityFactory $eavEntityFactory
     * @param Helper $resourceHelper
     * @param UniversalFactory $universalFactory
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param State $catalogProductFlatState
     * @param ScopeConfigInterface $scopeConfig
     * @param OptionFactory $productOptionFactory
     * @param Url $catalogUrl
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param Product $product
     * @param TypeFactory $eventTypeFactory
     * @param Type $productType
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteResource
     * @param Item $itemResource
     * @param System $helper
     * @param AdapterInterface|null $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        ResourceConnection $resource,
        EntityFactory $eavEntityFactory,
        Helper $resourceHelper,
        UniversalFactory $universalFactory,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        State $catalogProductFlatState,
        ScopeConfigInterface $scopeConfig,
        OptionFactory $productOptionFactory,
        Url $catalogUrl,
        TimezoneInterface $localeDate,
        Session $customerSession,
        DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        Product $product,
        TypeFactory $eventTypeFactory,
        Type $productType,
        \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteResource,
        Item $itemResource,
        System $helper,
        AdapterInterface $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $product,
            $eventTypeFactory,
            $productType,
            $quoteResource,
            $connection
        );
        $this->itemResource = $itemResource;
        $this->helper = $helper;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    private function getInventoryItemTable()
    {
        return $this->itemResource->getMainTable();
    }

    /**
     * @return string
     */
    private function getInventoryItemTableAlias()
    {
        return $this->inventoryItemTableAlias;
    }

    /**
     * @param $field
     *
     * @param null $alias
     * @return $this
     */
    private function addInventoryItemFieldToSelect($field, $alias = null)
    {
        if (empty($alias)) {
            $alias = $field;
        }

        if (isset($this->_joinFields[$alias])) {
            return $this;
        }

        $this->_joinFields[$alias] = ['table' => $this->getInventoryItemTableAlias(), 'field' => $field];

        $this->getSelect()->columns([$alias => $field], $this->getInventoryItemTableAlias());

        return $this;
    }

    /**
     * @param $field
     *
     * @return string
     */
    private function getInventoryItemField($field)
    {
        return sprintf('%s.%s', $this->getInventoryItemTableAlias(), $field);
    }

    /**
     * @param array $fields
     *
     * @return $this
     * @throws LocalizedException
     */
    public function joinInventoryItem($fields = [])
    {
        if (!$this->inventoryItemJoined) {
            $this->getSelect()->joinLeft(
                [$this->getInventoryItemTableAlias() => $this->getInventoryItemTable()],
                sprintf(
                    'e.%s = %s.product_id',
                    $this->getEntity()->getEntityIdField(),
                    $this->getInventoryItemTableAlias()
                ),
                []
            );
            $this->inventoryItemJoined = true;
        }

        if (!is_array($fields)) {
            if (empty($fields)) {
                $fields = [];
            } else {
                $fields = [$fields];
            }
        }

        foreach ($fields as $alias => $field) {
            if (!is_string($alias)) {
                $alias = null;
            }
            $this->addInventoryItemFieldToSelect($field, $alias);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function useNotifyStockQtyFilter()
    {
        $this->joinInventoryItem(['qty']);
        $lowStock = $this->helper->getLowStock();
        $this->getSelect()->where($this->getInventoryItemField('qty') . ' <= ?', $lowStock);

        return $this;
    }

    /**
     * @return $this
     */
    public function setSimpleType()
    {
        $this->addAttributeToFilter('type_id', 'simple');

        return $this;
    }

    /**
     * @param $items
     *
     * @return $this
     */
    public function setWarehouses($items)
    {
        if ($items) {
            $this->getSelect()->where($this->getInventoryItemField('warehouse_id') . ' IN(?)', $items);
        }
        return $this;
    }
}
