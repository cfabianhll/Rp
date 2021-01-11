<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magestore\Stocktaking\Model\Export\CsvExportHandler;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\CollectionFactory
    as SourceProductCollectionFactory;

/**
 * Class ExportNotCounted
 *
 * Used for export not counted
 */
class ExportNotCounted extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::edit_stocktaking';

    /**
     * @var SourceProductCollectionFactory
     */
    protected $sourceProductCollectionFactory;

    /**
     * @var CsvExportHandler
     */
    protected $csvExportHandler;

    /**
     * ExportNotCounted constructor.
     *
     * @param Context $context
     * @param SourceProductCollectionFactory $sourceProductCollectionFactory
     * @param CsvExportHandler $csvExportHandler
     */
    public function __construct(
        Context $context,
        SourceProductCollectionFactory $sourceProductCollectionFactory,
        CsvExportHandler $csvExportHandler
    ) {
        $this->sourceProductCollectionFactory = $sourceProductCollectionFactory;
        $this->csvExportHandler = $csvExportHandler;
        parent::__construct($context);
    }

    /**
     * Export different list
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        return $this->csvExportHandler->createDownloadResponse(
            'uncounted_product_list_stock_take',
            $this->csvExportHandler->getUncountedCsvData(
                $this->sourceProductCollectionFactory->create()
                    ->getUncountedSkuStocktaking(
                        (int)$this->getRequest()->getParam('id')
                    )
            )
        );
    }
}
