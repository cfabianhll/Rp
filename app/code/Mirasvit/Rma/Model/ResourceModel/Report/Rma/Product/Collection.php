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



namespace Mirasvit\Rma\Model\ResourceModel\Report\Rma\Product;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\ReportFactory
     */
    protected $reportFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $periodFormat;

    /**
     * @var array
     */
    protected $selectedColumns = [];

    /**
     * Collection constructor.
     * @param \Magento\Sales\Model\ResourceModel\ReportFactory $reportFactory
     * @param \Mirasvit\Rma\Api\Config\RmaConfigInterface $config
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
            $this->getSelect()->where($this->periodFormat.' >= ?', $this->_from);
        }
        if ($this->_to !== null) {
            $this->getSelect()->where($this->periodFormat.' <= ?', $this->_to);
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
            $this->periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->periodFormat = 'EXTRACT(YEAR FROM main_table.created_at)';
        } else {
            $this->periodFormat = 'DATE_FORMAT(main_table.created_at, \'%Y-%m-%d\')';
        }

        $this->selectedColumns = [
            'created_at' => $this->periodFormat,
            'qty_returns' => 'count(qty_requested)',
            'qty_items' => 'sum(qty_requested)',
        ];

        return $this->selectedColumns;
    }

    /**
     * {@inheritdoc}
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

        // alias then field name
        $productAttributes = ['product_name' => 'name'];
        foreach ($productAttributes as $alias => $attributeCode) {
            $tableAlias = $attributeCode.'_table';
            $attribute = $this->config
            ->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

            //Add eav attribute value
            $this->getSelect()->joinLeft(
                [$tableAlias => $attribute->getBackendTable()],
                "main_table.product_id = $tableAlias.entity_id AND $tableAlias.attribute_id={$attribute->getId()}",
                [$alias => 'value']
            );
        }
        $select->where('qty_requested > 0');

        if (!$this->isTotals() && !$this->isSubTotals()) {
            //the field on which the grouping
            // is made in the report output
            $select->group([
                $this->periodFormat,
                'product_id',
            ]);
        }
        if ($this->isSubTotals()) {
            $select->group([
                $this->periodFormat,
            ]);
        }

        // echo $this->getSelect();
        return $this;
    }

    /************************/
}
