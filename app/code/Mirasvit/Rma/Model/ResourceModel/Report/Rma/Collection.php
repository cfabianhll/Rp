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



namespace Mirasvit\Rma\Model\ResourceModel\Report\Rma;

class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\ReportFactory
     */
    protected $reportFactory;

    /**
     * @var string
     */
    protected $periodFormat;

    /**
     * @var string
     */
    protected $reportType;

    /**
     * @var array
     */
    protected $selectedColumns = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory
     */
    private $statusCollectionFactory;

    /**
     * @param \Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);

        $this->setModel('adminhtml/report_item');
        $this->_resource = $this->reportFactory->create()->init('rma/rma');
        $this->setConnection($this->getResource()->getReadConnection());

        $this->statusCollectionFactory = $statusCollectionFactory;
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
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $storeIds = array_unique($storeIds);

        $index = array_search(null, $storeIds);
        if ($index !== false) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $this->getSelect()->where('store_id IN(?) OR store_id IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()->where('store_id IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * @param array $filterData
     *
     * @return $this
     */
    public function setFilterData($filterData)
    {
        if (isset($filterData['report_type'])) {
            $this->reportType = $filterData['report_type'];
        } else {
            $this->reportType = 'all';
        }

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
                'total_rma_cnt' => 'COUNT(*)',
                'total_product_cnt' => 'SUM(rma_item.qty_requested)',
            ];
        $statusCollection = $this->statusCollectionFactory->create()->addActiveFilter();
        foreach ($statusCollection as $status) {
            $this->selectedColumns["{$status->getId()}_cnt"] = "SUM(if (status_id = {$status->getId()}, 1, 0))";
        }
        if ($this->reportType == 'by_product') {
            $this->selectedColumns['product_id'] = 'rma_item.product_id';
        }

        return $this->selectedColumns;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $select = $this->getSelect();
        $select->from(['main_table' => $this->getResource()->getMainTable()], $this->_getSelectedColumns());

        if (!$this->isTotals() && !$this->isSubTotals()) {
            //the field on which the grouping
            // is made in the report output
            $select->group([
                $this->periodFormat,
            ]);
            if ($this->reportType == 'by_product') {
                $select->group('product_id');
            }
        }
        if ($this->isSubTotals()) {
            $select->group([
                $this->periodFormat,
            ]);
        }
        $select->joinLeft(['rma_item' => $this->getTable('mst_rma_item')], 'main_table.rma_id = rma_item.rma_id', []);

        return $this;
    }

    /************************/
}
