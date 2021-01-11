<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;

/**
 * Source product collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Accept ignore because the problem is belong to parent class
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    const MAPPING_FIELD = [
        // Remove trailing 0 in decimal
        'qty_in_source' => "TRIM(TRAILING '.' FROM TRIM(TRAILING '0' from "
            . "IFNULL(current_inventory_source_item.quantity, 0)"
            . "))",
        'barcode' => 'GROUP_CONCAT(DISTINCT barcode.barcode)'
    ];

    /**
     * Init Select
     *
     * @return $this|\Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSelect()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\Module\Manager $moduleManager */
        $moduleManager = $om->get(\Magento\Framework\Module\Manager::class);

        $this->getSelect()->from(['e' => $this->getEntity()->getEntityTable()]);
        $entity = $this->getEntity();
        if ($entity->getTypeId() && $entity->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE) {
            $this->addAttributeToFilter('entity_type_id', $this->getEntity()->getTypeId());
        }
        $this->addAttributeToFilter(
            'type_id',
            [
                'nin' => [
                    BundleType::TYPE_CODE,
                    Configurable::TYPE_CODE,
                    Grouped::TYPE_CODE
                ]
            ]
        );
        $this->addAttributeToSelect(
            [
                "name",
                "sku"
            ]
        );

        $resource = $this->getResource();
        $sourceItemTable = $resource->getTable('inventory_source_item');

        // Add current quantity
        $currentSource = $this->getCurrentStocktakingSourceCode();

        if ($currentSource) {
            $this->getSelect()->columns([
                'counted_qty' => new \Zend_Db_Expr('0'),
                'qty_in_source' => new \Zend_Db_Expr(self::MAPPING_FIELD['qty_in_source'])
            ]);
            $this->getSelect()->joinInner(
                ['current_inventory_source_item' => $sourceItemTable],
                "e.sku = current_inventory_source_item.sku AND
                current_inventory_source_item.source_code = '$currentSource'",
                ['quantity']
            );
        }

        // add barcode
        $barcodeTable = $resource->getTable('os_barcode');
        if ($moduleManager->isEnabled('Magestore_BarcodeSuccess')) {
            $this->getSelect()->columns([
                'barcode' => new \Zend_Db_Expr(self::MAPPING_FIELD['barcode']),
                'barcode_original_data' => new \Zend_Db_Expr(self::MAPPING_FIELD['barcode'])
            ]);
            $this->getSelect()->joinLeft(
                ['barcode' => $barcodeTable],
                "e.sku = barcode.product_sku",
                []
            );
        }

        $this->getSelect()->group('e.sku');

        return $this;
    }

    /**
     * Get source code of current stock-taking
     *
     * @return string|null
     */
    public function getCurrentStocktakingSourceCode()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $om->get(\Magento\Framework\App\RequestInterface::class);

        $sourceCode = $request->getParam('source_code');
        if ($sourceCode) {
            return $sourceCode;
        }
        return null;
    }

    /**
     * Get Select Count Sql
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Zend_Db_Select_Exception
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        if (!count($this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP))) {
            $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
            return $countSelect;
        }
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT ".implode(", ", $group).")")));
        return $countSelect;
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelect()
                ->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
                ->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
                ->__toString();
            $records = $this->getConnection()->query($sql);
            $result = $records->fetchAll();
            $this->_totalRecords = count($result);
        }
        return (int)$this->_totalRecords;
    }

    /**
     * @inheritDoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        foreach (self::MAPPING_FIELD as $key => $value) {
            if ($field == $key) {
                $field = $value;
                return $this->addFieldToFilterCallBack($field, $condition);
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add Field To Filter Call Back
     *
     * @param string $field
     * @param integer|string|array $condition
     */
    public function addFieldToFilterCallBack(string $field, $condition)
    {
        $count = count($condition);
        for ($i = 0; $i < $count; $i++) {
            $conditionSql = $this->_getConditionSql($field, $condition);
            $this->getSelect()->having($conditionSql);
        }
    }

    /**
     * Add Barcode To Filter
     *
     * @param string $barcode
     * @return $this
     */
    public function addBarcodeToFilter(string $barcode)
    {
        $this->getSelect()->where(
            $this->getConnection()->prepareSqlCondition('barcode.barcode', ['like' => $barcode])
        );
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        foreach (self::MAPPING_FIELD as $key => $value) {
            if ($field == $key) {
                $field = $value;
                $this->getSelect()->order(new \Zend_Db_Expr($field .' '. $direction));
                return $this;
            }
        }
        return parent::addOrder($field, $direction);
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        foreach (self::MAPPING_FIELD as $key => $value) {
            if ($field == $key) {
                $field = $value;
            }
        }
        return parent::setOrder($field, $direction);
    }

    /**
     * Get uncounted sku
     *
     * @param int $stocktakingId
     * @return $this
     */
    public function getUncountedSkuStocktaking(int $stocktakingId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemCollection = $objectManager->get(StocktakingItemRepositoryInterface::class)
            ->getListByStocktakingId($stocktakingId);
        $values = $itemCollection->getColumnValues(StocktakingItemInterface::PRODUCT_ID);
        if (count($values)) {
            $this->addFieldToFilter(
                'entity_id',
                [
                    'nin' => $values
                ]
            );
        }
        return $this;
    }

    /**
     * Get different not in stocktaking
     *
     * @param int $stocktakingId
     * @return $this
     */
    public function getDifferentNotInStocktaking(int $stocktakingId)
    {
        $this->getUncountedSkuStocktaking($stocktakingId)->getSelect()
            ->where(self::MAPPING_FIELD['qty_in_source'].' > 0');
        return $this;
    }
}
