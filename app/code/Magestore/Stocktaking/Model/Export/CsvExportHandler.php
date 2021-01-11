<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory as FileFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\StocktakingArchiveItem\Collection
    as ArchiveItemCollection;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection
    as ItemCollection;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\Collection as SourceProductCollection;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\StocktakingArchiveItem\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class CsvExportHandler
 *
 * Used for export csv
 */
class CsvExportHandler
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CsvExportHandler constructor.
     *
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->filesystem = $filesystem;
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * Create download response file
     *
     * @param string $downloadFileName
     * @param array $data
     * @return \Magento\Framework\App\ResponseInterface|void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createDownloadResponse(string $downloadFileName, array $data)
    {
        $name = $downloadFileName . sha1(microtime());
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('export');
        $filename = DirectoryList::VAR_DIR . '/export/' . $name . '.csv';
        $stream = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->openFile($filename, 'w+');
        $stream->lock();

        foreach ($data as $row) {
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();

        try {
            return $this->fileFactory->create(
                $downloadFileName.'.csv',
                [
                    'type' => 'filename',
                    'value' => $filename,
                    'rm' => true  // can delete file after use
                ],
                DirectoryList::VAR_DIR
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get csv data
     *
     * @param ArchiveItemCollection $collection
     * @return array
     */
    public function getArchiveItemCsvData(ArchiveItemCollection $collection)
    {
        $data = [
            [
                __('SKU'),
                __('NAME'),
                __('CURRENT QUANTITY'),
                __('COUNTED QUANTITY'),
                __('REASON OF DIFFERENCE')
            ]
        ];
        foreach ($collection as $stocktakingItem) {
            $data[] = [
                $stocktakingItem->getProductSku(),
                $stocktakingItem->getProductName(),
                $stocktakingItem->getQtyInSource(),
                $stocktakingItem->getCountedQty(),
                $stocktakingItem->getDifferenceReason()
            ];
        }
        return $data;
    }

    /**
     * Get csv data
     *
     * @param ItemCollection $collection
     * @param SourceProductCollection|null $notCountedProduct
     * @return array
     */
    public function getItemDifferentCsvData(ItemCollection $collection, $notCountedProduct = null)
    {
        $data = [
            [
                __('SKU'),
                __('CURRENT QUANTITY'),
                __('COUNTED QUANTITY'),
                __('REASON OF DIFFERENCE')
            ]
        ];
        foreach ($collection as $stocktakingItem) {
            $data[] = [
                $stocktakingItem->getProductSku(),
                $stocktakingItem->getQtyInSource(),
                $stocktakingItem->getCountedQty(),
                $stocktakingItem->getDifferenceReason()
            ];
        }

        if ($notCountedProduct) {
            foreach ($notCountedProduct as $sourceProduct) {
                $data[] = [
                    $sourceProduct->getSku(),
                    $sourceProduct->getQtyInSource(),
                    0,
                    ''
                ];
            }
        }

        return $data;
    }

    /**
     * Get uncounted csv data
     *
     * @param SourceProductCollection $notCountedProduct
     * @return array
     */
    public function getUncountedCsvData(SourceProductCollection $notCountedProduct)
    {
        $data = [
            [
                __('SKU'),
                __('COUNTED QUANTITY'),
                __('REASON OF DIFFERENCE')
            ]
        ];

        foreach ($notCountedProduct as $sourceProduct) {
            $data[] = [
                $sourceProduct->getSku(),
                '',
                ''
            ];
        }

        return $data;
    }
}
