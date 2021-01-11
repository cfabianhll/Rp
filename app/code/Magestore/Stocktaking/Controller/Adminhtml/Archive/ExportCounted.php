<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Archive;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveItemInterface;
use Magestore\Stocktaking\Model\Export\CsvExportHandler;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\StocktakingArchiveItem\CollectionFactory;

/**
 * Class ExportCounted
 *
 * Used for export counted
 */
class ExportCounted extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::view_archived_stocktaking_detail';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CsvExportHandler
     */
    protected $csvExportHandler;

    /**
     * ExportCounted constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param CsvExportHandler $csvExportHandler
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CsvExportHandler $csvExportHandler
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->csvExportHandler = $csvExportHandler;
        parent::__construct($context);
    }

    /**
     * Export counted
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        return $this->csvExportHandler->createDownloadResponse(
            'counted_product_list_item',
            $this->csvExportHandler->getArchiveItemCsvData(
                $this->collectionFactory->create()
                    ->addFieldToFilter(
                        StocktakingArchiveItemInterface::STOCKTAKING_ID,
                        $this->getRequest()->getParam('id')
                    )
            )
        );
    }
}
