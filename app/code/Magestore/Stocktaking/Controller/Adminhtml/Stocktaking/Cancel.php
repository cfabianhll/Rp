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

/**
 * Class Cancel
 *
 * Use to cancel stock-taking
 */
class Cancel extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::cancel';

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

        try {
            $result = $this->stocktakingRepositoryInterface->cancel($id);
            if ($result) {
                $this->messageManager->addSuccessMessage(__("You have canceled the stock-taking successfully."));
                $resultRedirect->setPath('*/*/');
            } else {
                $this->messageManager->addErrorMessage(__('The stock-taking cannot be canceled.'));
                $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->messageManager->addErrorMessage(__('The stock-taking cannot be canceled.'));
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        }
        return $resultRedirect;
    }
}
