<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Import
 *
 * Process import csv to stock-taking
 */
class Import extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::edit_stocktaking';

    /**
     * @var \Magestore\Stocktaking\Model\Import\CsvImportHandler
     */
    protected $csvImportHandler;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Import constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magestore\Stocktaking\Model\Import\CsvImportHandler $csvImportHandler
     * @param \Magento\Backend\Model\Session $backendSession
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magestore\Stocktaking\Model\Import\CsvImportHandler $csvImportHandler,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->csvImportHandler = $csvImportHandler;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Used for import csv
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $stocktakeId = (int) $this->getRequest()->getParam('id');
        if ($stocktakeId) {
            try {
                $result = $this->csvImportHandler->importFromCsvFile(
                    $this->getRequest()->getFiles('import_product'),
                    $stocktakeId
                );

                $invalidData = $result['invalidData'];
                $countSuccess = $result['countSuccess'];
                if (count($invalidData)) {
                    $this->backendSession->setData('is_error_' . $stocktakeId, true);
                    $this->backendSession->setData('data_invalid_' . $stocktakeId, $invalidData);
                }

                if ($countSuccess) {
                    $this->messageManager->addSuccessMessage(
                        __('You have updated %1 item(s) to stock-taking.', $countSuccess)
                    );
                } else {
                    $this->messageManager->addErrorMessage(__('Could not import products. Please try again.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}
