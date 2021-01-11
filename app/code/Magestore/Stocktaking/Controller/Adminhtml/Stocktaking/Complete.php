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
use Magento\Backend\App\Action;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Class Complete
 *
 * Use to Complete stock-taking
 */
class Complete extends Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::complete';

    /**
     * @var StocktakingRepositoryInterface
     */
    protected $stocktakingRepositoryInterface;

    /**
     * Cancel constructor
     *
     * @param Context $context
     * @param StocktakingRepositoryInterface $stocktakingRepositoryInterface
     */
    public function __construct(
        Context $context,
        StocktakingRepositoryInterface $stocktakingRepositoryInterface
    ) {
        parent::__construct($context);
        $this->stocktakingRepositoryInterface = $stocktakingRepositoryInterface;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = (int) $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$id) {
            return $resultRedirect->setPath('*/*/');
        }

        /** @var StocktakingInterface $model */
        $model = $this->stocktakingRepositoryInterface->load($id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This stock-taking no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }

        $createAdjustStock = (bool) $this->getRequest()->getParam('createAdjustStock');
        $result = $this->stocktakingRepositoryInterface->complete($id, $createAdjustStock);
        if ($result['status']) {
            $this->messageManager->addSuccessMessage($result['message']);
            $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage($result['message']);
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        }
        
        return $resultRedirect;
    }
}
