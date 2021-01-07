<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Model\ResourceModel\Report\Rma\Brand;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    /**
     * @var array
     */
    protected $selectedColumns = [];

    /**
     * @var string
     */
    protected $periodFormatreportType;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;
    /**
     * @var \Mirasvit\Rma\Api\Config\RmaConfigInterface
     */
    private $config;
    /**
     * @var \Magento\Sales\Model\ResourceModel\ReportFactory
     */
    private $reportFactory;

    /**
     * Collection constructor.
     * @param \Magento\Sales\Model\ResourceModel\ReportFactory $reportFactory
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $config
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\ReportFactory $reportFactory,
        \Mirasvit\Rma\Api\Config\RmaConfigInterface $config,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
        parent::_construct();

        $this->reportFactory = $reportFactory;
        $this->config        = $config;
        $this->eavConfig     = $eavConfig;

        $this->setModel('adminhtml/report_item');
        $this->_resource = $this->reportFactory->create()->init('rma/item');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * {@inheritdoc}
     */
    protected function _applyDateRangeFilter()
    {
        if ($this->_from !== null) {
            $this->getSelect()->where($this->periodFormatreportType.' >= ?', $this->_from);
        }
        if ($this->_to !== null) {
            $this->getSelect()->where($this->periodFormatreportType.' <= ?', $this->_to);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function _applyStoresFilter()
    {
        return $this;
    }

    /**
     * @return array
     */
    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->periodFormatreportType = 'DATE_FORMAT(main_table.created_at, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->periodFormatreportType = 'EXTRACT(YEAR FROM main_table.created_at)';
        } else {
            $this->periodFormatreportType = 'DATE_FORMAT(main_table.created_at, \'%Y-%m-%d\')';
        }

        $this->selectedColumns = [
            'created_at' => $this->periodFormatreportType,
            'qty_returns' => 'count(qty_requested)',
            'qty_items' => 'sum(qty_requested)',
        ];

        return $this->selectedColumns;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();
        $select->from(['main_table' => $this->getResource()->getMainTable()], $this->_getSelectedColumns());

        $select->joinLeft(
            ['product' => $this->getTable('catalog_product')],
            'main_table.product_id = product.entity_id',
            ['product_sku' => 'product.sku']
        );
        $brandAttributeCode = $this->config->getGeneralBrandAttribute();
        if (!$brandAttributeCode) {
            throw new \Exception('Code of Brand Attribute is empty. Please, set it via RMA configuration.');
        }
        $attribute = $this->eavConfig
            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $brandAttributeCode);
        if (!$attribute->getId()) {
            throw new \Exception("Can't find attribute '<b>$brandAttributeCode</b>'. Please, check RMA configuration.");
        }

        // alias then field name
        $productAttributes = ['product_brand' => $brandAttributeCode];
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode.'_table';
            $attribute = $this->eavConfig
            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

            //Add eav attribute value
            $this->getSelect()->joinLeft(
                [$tableAlias => $attribute->getBackendTable()],
                "main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}",
                [$alias => 'value']
            );
            $tableOptionsAlias = $attributeCode.'_option_table';
            //Add eav attribute value
            $this->getSelect()->joinLeft(
                [$tableOptionsAlias => $this->_resource->getTableName('eav_attribute_option_value')],
                "$tableAlias.value = $tableOptionsAlias.option_id AND $tableOptionsAlias.store_id = 0",
                [$alias.'_option' => 'value']
            );
        }
        $select->where('qty_requested > 0');

        if (!$this->isTotals() && !$this->isSubTotals()) {
            //the field on which the grouping
            // is made in the report output
            $select->group([
                $this->periodFormatreportType,
                'product_brand',
            ]);
        }
        if ($this->isSubTotals()) {
            $select->group([
                $this->periodFormatreportType,
            ]);
        }

        return $this;
    }

    /************************/
}
