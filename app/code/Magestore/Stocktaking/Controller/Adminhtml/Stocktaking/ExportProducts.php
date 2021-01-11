<?php
/**
 * Copyright © 2019 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv as CsvProcessor;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface as FilesystemDriverInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection
    as StocktakingItemCollection;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\CollectionFactory
    as StocktakingItemCollectionFactory;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;

/**
 * Export products controller
 */
class ExportProducts extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization
     */
    const ADMIN_RESOURCE = 'Magestore_Stocktaking::edit_stocktaking';
    /**
     * @var CsvProcessor
     */
    protected $csvProcessor;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FilesystemDriverInterface
     */
    protected $filesystemDriver;

    /**
     * @var StocktakingItemCollectionFactory
     */
    protected $stocktakingItemCollectionFactory;

    /**
     * ExportProducts constructor.
     *
     * @param Context $context
     * @param CsvProcessor $csvProcessor
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param FilesystemDriverInterface $filesystemDriver
     * @param StocktakingItemCollectionFactory $stocktakingItemCollectionFactory
     */
    public function __construct(
        Context $context,
        CsvProcessor $csvProcessor,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        FilesystemDriverInterface $filesystemDriver,
        StocktakingItemCollectionFactory $stocktakingItemCollectionFactory
    ) {
        parent::__construct($context);
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->filesystemDriver = $filesystemDriver;
        $this->stocktakingItemCollectionFactory = $stocktakingItemCollectionFactory;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        $name = 'stocktaking_products_list.csv';
        $this->getBaseDirVar()->create('magestore/stocktaking');
        $filename = $this->getBaseDirVar()->getAbsolutePath('magestore/stocktaking/'.$name);
        $data = [
            [__('SKU'), __('COUNTED QUANTITY'), __('REASON OF DIFFERENCE')]
        ];
        $data = array_merge($data, $this->generateData());
        $this->csvProcessor->appendData($filename, $data);
        return $this->fileFactory->create(
            $name,
            $this->filesystemDriver->fileGetContents($filename),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * Get base dir var
     *
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getBaseDirVar()
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Generate data
     *
     * @return array
     */
    public function generateData()
    {
        $stocktakingId = $this->_request->getParam("id");
        $data = [];
        if ($stocktakingId) {
            /** @var StocktakingItemCollection $stocktakingItemCollection */
            $stocktakingItemCollection = $this->stocktakingItemCollectionFactory->create();
            $stocktakingItemCollection->addFieldToFilter(StocktakingItemInterface::STOCKTAKING_ID, $stocktakingId);
            foreach ($stocktakingItemCollection as $stocktakingItem) {
                $data[]= [
                    $stocktakingItem->getProductSku(),
                    $stocktakingItem->getCountedQty() ? $stocktakingItem->getCountedQty() : '',
                    $stocktakingItem->getDifferenceReason() ? $stocktakingItem->getDifferenceReason() : ''
                ];
            }
        }
        return $data;
    }
}
