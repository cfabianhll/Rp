<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Export;

use Amasty\MultiInventory\Model\Export\Export as WarehouseExport;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Catalog\Model\Product;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;

/**
 * Class Filter
 */
class Filter extends Extended
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Filter constructor.
     * @param Context $context
     * @param HelperData $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $backendHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setRowClickCallback(null);
        $this->setId('export_filter_grid');
        $this->setPagerVisibility(false);
        $this->setDefaultLimit(null);
        $this->setUseAjax(true);
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn(
            'field',
            [
                'header' => __('Field'),
                'sortable' => false,
                'filter' => false,
                'field_name' => 'code',
                'getter' => 'getField'
            ]
        );

        return $this;
    }

    /**
     * @param Product|DataObject $row
     *
     * @return bool
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * @return Collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $data = WarehouseExport::COLUMNS;
        foreach ($data as $option) {
            $varienObject = new DataObject();
            $item = [
                'field' => $option,
            ];
            $varienObject->setData($item);
            $collection->addItem($varienObject);
        }
        $this->setCollection($collection);

        return $this->getCollection();
    }
}
