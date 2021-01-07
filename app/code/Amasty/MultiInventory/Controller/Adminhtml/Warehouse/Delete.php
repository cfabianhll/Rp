<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Controller\Adminhtml\Warehouse;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 */
class Delete extends Warehouse
{
    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * Delete constructor.
     * @param Action\Context $context
     * @param WarehouseRepositoryInterface $repository
     * @param Processor $processor
     */
    public function __construct(
        Action\Context $context,
        WarehouseRepositoryInterface $repository,
        Processor $processor
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->processor = $processor;
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('warehouse_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->repository->getById($id);
                if (!$model->isGeneral()) {
                    $this->repository->deleteById($id);
                    $this->processor->reindexAll();
                    $this->messageManager->addSuccessMessage(__('You deleted the warehouse.'));
                } else {
                    $this->messageManager->addErrorMessage(__('We can\'t delete a warehouse'));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['warehouse_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a warehouse to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
