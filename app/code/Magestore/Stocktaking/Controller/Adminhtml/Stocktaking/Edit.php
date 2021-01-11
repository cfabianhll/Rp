<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magestore\Stocktaking\Service\Adminhtml\Stocktaking\Edit\GetCurrentStocktakingService;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterfaceFactory;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking as StocktakingResource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;

/**
 * Class Edit
 *
 * Use to create edit stock-taking
 */
class Edit extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::edit_stocktaking';

    /**
     * @var ResultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StocktakingInterfaceFactory
     */
    protected $stocktakingInterfaceFactory;

    /**
     * @var StocktakingResource
     */
    protected $stocktakingResource;

    /**
     * @var GetCurrentStocktakingService
     */
    protected $getCurrentStocktakingService;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param ResultPageFactory $resultPageFactory
     * @param StocktakingInterfaceFactory $stocktakingInterfaceFactory
     * @param StocktakingResource $stocktakingResource
     * @param GetCurrentStocktakingService $getCurrentStocktakingService
     */
    public function __construct(
        Context $context,
        ResultPageFactory $resultPageFactory,
        StocktakingInterfaceFactory $stocktakingInterfaceFactory,
        StocktakingResource $stocktakingResource,
        GetCurrentStocktakingService $getCurrentStocktakingService
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->stocktakingInterfaceFactory = $stocktakingInterfaceFactory;
        $this->stocktakingResource = $stocktakingResource;
        $this->getCurrentStocktakingService = $getCurrentStocktakingService;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {
            return $resultRedirect->setPath('*/*/');
        }
        /** @var StocktakingInterface $model */
        $model = $this->stocktakingInterfaceFactory->create();
        $this->stocktakingResource->load($model, $id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This stock-taking no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        if (!$this->checkPermission($model)) {
            $this->messageManager->addErrorMessage(__('Sorry, you need permissions to view this content.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->getCurrentStocktakingService->setCurrentStocktaking($model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Stock-taking "%1"', $model->getCode()));
        return $resultPage;
    }

    /**
     * Check Permission
     *
     * @param StocktakingInterface $model
     * @return bool
     */
    public function checkPermission(StocktakingInterface $model): bool
    {
        switch ($model->getStatus()) {
            case StocktakingInterface::STATUS_PREPARING:
                return $this->_authorization->isAllowed('Magestore_Stocktaking::prepare_product_list');
            case StocktakingInterface::STATUS_COUNTING:
                return $this->_authorization->isAllowed('Magestore_Stocktaking::count');
            case StocktakingInterface::STATUS_VERIFYING:
                return $this->_authorization->isAllowed('Magestore_Stocktaking::verify');
            default:
                return false;
        }
    }
}
