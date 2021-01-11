<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Model\Export\CsvExportHandler;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\CollectionFactory as ItemCollectionFactory;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\CollectionFactory
    as SourceProductCollectionFactory;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Class ExportDifferent
 *
 * Used for export different list
 */
class ExportDifferent extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::edit_stocktaking';

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var SourceProductCollectionFactory
     */
    protected $sourceProductCollectionFactory;

    /**
     * @var CsvExportHandler
     */
    protected $csvExportHandler;

    /**
     * ExportDifferent constructor.
     *
     * @param Context $context
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param SourceProductCollectionFactory $sourceProductCollectionFactory
     * @param CsvExportHandler $csvExportHandler
     */
    public function __construct(
        Context $context,
        ItemCollectionFactory $itemCollectionFactory,
        SourceProductCollectionFactory $sourceProductCollectionFactory,
        CsvExportHandler $csvExportHandler
    ) {
        $this->itemCollectionFactory = $itemCollectionFactory;
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
        if ($this->getRequest()->getParam('stock_taking_type') == StocktakingInterface::STOCKTAKING_TYPE_FULL) {
            $uncountedSku = $this->sourceProductCollectionFactory->create()->getDifferentNotInStocktaking(
                (int)$this->getRequest()->getParam('id')
            );
        } else {
            $uncountedSku = null;
        }
        return $this->csvExportHandler->createDownloadResponse(
            'different_product_list_stock_take',
            $this->csvExportHandler->getItemDifferentCsvData(
                $this->itemCollectionFactory->create()
                    ->addFieldToFilter(
                        StocktakingItemInterface::STOCKTAKING_ID,
                        (int)$this->getRequest()->getParam('id')
                    )->getDifferentCountedCollection(),
                $uncountedSku
            )
        );
    }
}
