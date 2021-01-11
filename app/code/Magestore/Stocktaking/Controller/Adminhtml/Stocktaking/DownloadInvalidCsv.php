<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

/**
 * Class DownloadInvalidCsv
 *
 * Used for download sample csv
 */
class DownloadInvalidCsv extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
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
     * Download file
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $stocktakeId = (int) $this->getRequest()->getParam('id');
        $invalidStocktakingData = $this->backendSession->getData('data_invalid_'.$stocktakeId, true);
        return $this->csvImportHandler->downloadInvalidStocktakingCsvFile($stocktakeId, $invalidStocktakingData);
    }
}
