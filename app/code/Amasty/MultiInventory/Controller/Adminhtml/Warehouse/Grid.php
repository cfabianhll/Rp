<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Product;
use Amasty\MultiInventory\Controller\Adminhtml\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Grid
 */
class Grid extends Warehouse
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * Grid constructor.
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param LayoutFactory $layoutFactory
     * @param Registry $registry
     * @param WarehouseFactory $warehouseFactory
     * @param WarehouseRepositoryInterface $repository
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        Registry $registry,
        WarehouseFactory $warehouseFactory,
        WarehouseRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * Get Grid
     *
     * @return $this|Redirect
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $warehouseId = (int)$this->getRequest()->getParam('warehouse_id', false);

        if ($warehouseId) {
            $warehouse = $this->repository->getById($warehouseId);
        } else {
            $warehouse = $this->warehouseFactory->create();
        }
        $this->registry->register('amasty_multi_inventory_warehouse', $warehouse);

        if (!$warehouse) {
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('warehouse/*/', ['_current' => true, 'warehouse_id' => null]);
        }
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                Product::class,
                'warehouse.product.grid'
            )->toHtml()
        );
    }
}
