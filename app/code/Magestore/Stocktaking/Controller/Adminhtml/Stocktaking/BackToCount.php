<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magestore\Stocktaking\Api\StocktakingRepositoryInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;

/**
 * Class BackToCount
 *
 * Use to move stock-taking back to count step
 */
class BackToCount extends Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::verify';

    /**
     * @var StocktakingRepositoryInterface
     */
    protected $stocktakingRepository;

    /**
     * Cancel constructor
     *
     * @param Context $context
     * @param StocktakingRepositoryInterface $stocktakingRepository
     */
    public function __construct(
        Context $context,
        StocktakingRepositoryInterface $stocktakingRepository
    ) {
        parent::__construct($context);
        $this->stocktakingRepository = $stocktakingRepository;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        /** @var ResultRedirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {
            return $resultRedirect->setPath('*/*/');
        }

        try {
            /** @var StocktakingInterface $model */
            $model = $this->stocktakingRepository->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This stock-taking no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            if ($model->getStatus() != StocktakingInterface::STATUS_VERIFYING) {
                $this->messageManager->addErrorMessage(__('The stock-taking can not back to count.'));
            } else {
                $model->setStatus(StocktakingInterface::STATUS_COUNTING);
                $this->stocktakingRepository->save($model);
                $this->messageManager->addSuccessMessage(__("The stock-taking has been ready to count."));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addErrorMessage(__('The stock-taking can not back to count.'));
        }

        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
