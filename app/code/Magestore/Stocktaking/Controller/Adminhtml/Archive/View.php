<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Archive;

use Magestore\Stocktaking\Service\Adminhtml\Archive\View\CurrentStocktakingArchiveService;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterfaceFactory;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive as StocktakingArchiveResource;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;

/**
 * Class View
 *
 * Use to view archived stock-taking
 */
class View extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::view_archived_stocktaking_details';

    /**
     * @var ResultPageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StocktakingArchiveInterfaceFactory
     */
    protected $stocktakingArchiveInterfaceFactory;

    /**
     * @var StocktakingArchiveResource
     */
    protected $stocktakingArchiveResource;

    /**
     * @var CurrentStocktakingArchiveService
     */
    protected $currentStocktakingArchiveService;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param ResultPageFactory $resultPageFactory
     * @param StocktakingArchiveInterfaceFactory $stocktakingArchiveInterfaceFactory
     * @param StocktakingArchiveResource $stocktakingArchiveResource
     * @param CurrentStocktakingArchiveService $currentStocktakingArchiveService
     */
    public function __construct(
        Context $context,
        ResultPageFactory $resultPageFactory,
        StocktakingArchiveInterfaceFactory $stocktakingArchiveInterfaceFactory,
        StocktakingArchiveResource $stocktakingArchiveResource,
        CurrentStocktakingArchiveService $currentStocktakingArchiveService
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->stocktakingArchiveInterfaceFactory = $stocktakingArchiveInterfaceFactory;
        $this->stocktakingArchiveResource = $stocktakingArchiveResource;
        $this->currentStocktakingArchiveService = $currentStocktakingArchiveService;
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
        /** @var StocktakingArchiveInterface $model */
        $model = $this->stocktakingArchiveInterfaceFactory->create();
        $this->stocktakingArchiveResource->load($model, $id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This stock-taking no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $this->currentStocktakingArchiveService->setCurrentStocktakingArchive($model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Stock-taking "%1"', $model->getCode()));
        return $resultPage;
    }
}
