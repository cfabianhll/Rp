<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Lowstock;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Lowstock\CollectionFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Helper\Data;
use Magento\Framework\Data\Collection;

/**
 * Class Grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * @var CollectionFactory
     */
    private $lowstocksFactory;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var string
     */
    protected $_template = 'Amasty_MultiInventory::grid.phtml';

    /**
     * @var array
     */
    private $warehouses;

    /**
     * @var array
     */
    protected $_defaultFilter = ['report_warehouse' => ''];

    /**
     * Grid constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $lowstocksFactory
     * @param WarehouseFactory $warehouseFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $lowstocksFactory,
        WarehouseFactory $warehouseFactory,
        array $data = []
    ) {
        $this->lowstocksFactory = $lowstocksFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->_defaultFilter['report_warehouse'] = [$this->warehouseFactory->create()->getDefaultId()];
        $this->escaper = $context->getEscaper();
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->lowstocksFactory->create()->addAttributeToSelect('*')
            ->joinInventoryItem('qty')
            ->setSimpleType()
            ->useNotifyStockQtyFilter()
            ->setOrder(
                'qty',
                Collection::SORT_ORDER_ASC
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return array
     */
    public function getWarehouses()
    {
        if ($this->warehouses == null) {
            $collection = $this->warehouseFactory->create()
                ->getCollection()
                ->clear()
                ->addFieldToSelect('warehouse_id')
                ->addFieldToSelect('title')
                ->toArray();

            foreach ($collection['items'] as $item) {
                $this->warehouses[$item['warehouse_id']] = $item['title'];
            }
        }

        return $this->warehouses;
    }

    /**
     * @return string
     */
    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepareFilterButtons()
    {
        parent::_prepareFilterButtons();
        $this->addChild(
            'refresh_button',
            Button::class,
            ['label' => __('Refresh'), 'onclick' => "{$this->getJsObjectName()}.doFilter();", 'class' => 'task']
        );
    }

    /**
     * @param array $data
     * @return $this|\Magento\Backend\Block\Widget\Grid
     */
    protected function _setFilterValues($data)
    {
        $warehouse = (isset($data['report_warehouse'])
            ? $data['report_warehouse']
            : $this->warehouseFactory->create()->getDefaultId());
        $this->getCollection()->setWarehouses($warehouse);
        $this->setFilter('report_warehouse', [$warehouse]);

        return parent::_setFilterValues($data);
    }

    /**
     * Set filter
     *
     * @param string $name
     * @param string $value
     * @return void
     * @codeCoverageIgnore
     */
    public function setFilter($name, $value)
    {
        if ($name) {
            $this->filters[$name] = $value;
        }
    }

    /**
     * Get filter by key
     *
     * @param string $name
     * @return string|array
     */
    public function getFilter($name)
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name];
        } else {
            return $this->getRequest()->getParam($name)
                ? $this->escaper->escapeHtml($this->getRequest()->getParam($name))
                : '';
        }
    }
}
