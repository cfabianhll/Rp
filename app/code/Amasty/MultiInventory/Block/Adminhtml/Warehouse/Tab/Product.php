<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab;

use Amasty\MultiInventory\Model\Config\Source\Backorders;
use Amasty\MultiInventory\Model\Config\Source\Stock;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\ColumnSet;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as DataHelper;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Product
 */
class Product extends Extended
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var string
     */
    protected $_template = 'Amasty_MultiInventory::widget/grid/extended.phtml';

    /**
     * @var Phrase
     */
    protected $emptyText;

    /**
     * @var Stock
     */
    private $stockOptions;

    /**
     * @var Backorders
     */
    private $backordersOptions;

    /**
     * @var \Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Data
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param \Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Data $backendHelper
     * @param ProductFactory $productFactory
     * @param Registry $coreRegistry
     * @param Stock $stockOptions
     * @param Backorders $backordersOptions
     * @param DataHelper $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductFactory $productFactory,
        Registry $coreRegistry,
        Stock $stockOptions,
        Backorders $backordersOptions,
        DataHelper $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->emptyText = __('There are no assigned products yet. To add the stock to the warehouse click on the product and fill in the quantity.');
        $this->productFactory = $productFactory;
        $this->coreRegistry = $coreRegistry;
        $this->stockOptions = $stockOptions;
        $this->backordersOptions = $backordersOptions;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amasty_multi_inventory_warehouse');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getWarehouse()
    {
        return $this->coreRegistry->registry('amasty_multi_inventory_warehouse');
    }

    public function getColumnSet()
    {
        if (!$this->getChildBlock('grid.columnSet')) {
            $this->setChild(
                'grid.columnSet',
                $this->getLayout()->createBlock(ColumnSet::class)
            );
        }
        return $this->getChildBlock('grid.columnSet');
    }

    /**
     * @param Column $column
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_warehouse') {
            $warehouse_id = (int)$this->getRequest()->getParam('warehouse_id', 0);
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => [$productIds]]);
            } elseif (!$warehouse_id) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => [0]]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getWarehouse()->getId()) {
            $this->setDefaultFilter(['in_warehouse' => 1]);
        } else {
            $this->setDefaultFilter(['in_warehouse' => 0]);
        }

        $collection = $this->productFactory->create()->getCollection()
            ->addFieldToFilter('type_id', Type::TYPE_SIMPLE)
            ->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'sku'
            )->addAttributeToSelect(
                'price'
            )->joinTable(
                'amasty_multiinventory_warehouse_item',
                'product_id=entity_id',
                [
                    'aqty' => 'qty',
                    'ship_qty' => 'ship_qty',
                    'available_qty' => 'available_qty',
                    'room_shelf' => 'room_shelf',
                    'stock_status' => 'stock_status',
                    'backorders' => 'backorders'
                ],
                sprintf('warehouse_id=%s', (int)$this->getRequest()->getParam('warehouse_id', 0)),
                'left'
            );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->setGeneralTotals();
        return $this;
    }

    /**
     * @return array
     */
    public function getGeneralTotals()
    {
        $select = $this->getCollection()->getSelect();
        $connection = $this->getCollection()->getConnection();
        $select->reset(Select::COLUMNS);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);
        $select->columns([
            'qty' => new \Zend_Db_Expr('SUM(qty)'),
            'ship_qty' => new \Zend_Db_Expr('SUM(ship_qty)'),
            'available_qty' => new \Zend_Db_Expr('SUM(available_qty)')
        ]);
        $data = [];
        $records = $connection->fetchAssoc($select->__toString());

        foreach ($records as $record) {
            foreach ($record as $index => $value) {
                $data[$index] = ['label' => $this->getColumn($index)->getData('header'), 'value' => (int)$value];
            }
            break;
        }

        return $data;
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_warehouse',
            [
                'type' => 'checkbox',
                'name' => 'in_warehouse',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    Currency::XML_PATH_CURRENCY_BASE,
                    ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'qty',
            [
                'header' => __('Total Qty'),
                'type' => 'number',
                'index' => 'aqty',
                'editable' => true
            ]
        );

        $this->addColumn(
            'ship_qty',
            [
                'header' => __('Qty To Ship'),
                'type' => 'number',
                'index' => 'ship_qty'
            ]
        );

        $this->addColumn(
            'available_qty',
            [
                'header' => __('Available Qty'),
                'type' => 'number',
                'index' => 'available_qty'
            ]
        );
        $this->addColumn(
            'room_shelf',
            [
                'header' => __('Room & Shelf'),
                'type' => 'string',
                'index' => 'room_shelf',
                'editable' => true
            ]
        );
        $this->addColumn(
            'stock_status',
            [
                'header' => __('Stock Status'),
                'type' => 'select',
                'index' => 'stock_status',
                'options' =>  $this->stockOptions->toFlatArray(),
                'editable' => true,
            ]
        );
        $this->addColumn(
            'backorders',
            [
                'header' => __('Backorders'),
                'type' => 'select',
                'index' => 'backorders',
                'options' =>  $this->backordersOptions->getGridOptions(),
                'editable' => true,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $products = $this->getWarehouse()->getProductsToGrid();
            return array_keys($products);
        }
        return $products;
    }

    /**
     * @param $id
     *
     * @return string
     */
    public function jsonEncode($id)
    {
        return $this->dataHelper->jsonEncode($id);
    }
}
