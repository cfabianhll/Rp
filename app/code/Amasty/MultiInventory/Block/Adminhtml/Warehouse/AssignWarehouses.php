<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Warehouse;

use Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Warehouse;
use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class AssignWarehouses
 */
class AssignWarehouses extends Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'warehouse/edit/assign_warehouses.phtml';

    /**
     * @var Warehouse
     */
    protected $blockGrid;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var CollectionFactory
     */
    private $factory;

    /**
     * @var WarehouseFactory
     */
    private $wh;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @var System
     */
    private $system;

    /**
     * AssignWarehouses constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $factory
     * @param WarehouseFactory $wh
     * @param Data $helper
     * @param EncoderInterface $jsonEncoder
     * @param System $system
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $factory,
        WarehouseFactory $wh,
        Data $helper,
        EncoderInterface $jsonEncoder,
        System $system,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->factory = $factory;
        $this->wh = $wh;
        $this->helper = $helper;
        $this->system = $system;
    }

    /**
     * Retrieve instance of grid block
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                Tab\Warehouse::class,
                'warehouse.grid'
            );
        }

        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     * @throws LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getProductsJson()
    {
        $list = $this->wh->create()->getWhNotActive();
        $warehouses = $this->factory->create()
            ->addFieldToFilter('product_id', $this->getProduct()->getId())
            ->addFieldToSelect(['warehouse_id', 'qty', 'room_shelf', 'backorders', 'stock_status']);
        if (count($list) > 0) {
            $warehouses->addFieldToFilter('warehouse_id', ['nin' => $list]);
        }
        $data = [];
        if (!empty($warehouses)) {
            /** @var Item $item */
            foreach ($warehouses as $item) {
                if (!$item->getWarehouse()->isGeneral()) {
                    $data[$item->getWarehouseId()] = $item->getData();
                }
            }

            return $this->jsonEncoder->encode($data);
        }

        return '{}';
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return int
     */
    public function isAvailable()
    {
        return (int)$this->system->getAvailableDecreese();
    }
}
