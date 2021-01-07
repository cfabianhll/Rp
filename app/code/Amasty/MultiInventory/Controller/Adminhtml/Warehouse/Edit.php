<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Controller\Adminhtml\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Edit
 */
class Edit extends Warehouse
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param WarehouseFactory $warehouseFactory
     * @param WarehouseRepositoryInterface $repository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        WarehouseFactory $warehouseFactory,
        WarehouseRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->warehouseFactory = $warehouseFactory;
        $this->repository = $repository;
    }

    /**
     * Index action
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('warehouse_id');
        if ($id) {
            try {
                $model = $this->repository->getById($id);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This warehouse no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $model = $this->warehouseFactory->create();
        }
        if ($model->getStockId() == Stock::DEFAULT_STOCK_ID) {
            $this->messageManager->addErrorMessage(__('You can\'t edit the warehouse.'));
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }

        $this->coreRegistry->register('amasty_multi_inventory_warehouse', $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_MultiInventory::warehouses');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Warehouse') : __('New Warehouse'),
            $id ? __('Edit Warehouse') : __('New Warehouse')
        );
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Warehouse'));

        return $resultPage;
    }
}
