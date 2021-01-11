<?php
/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory as FileFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class GenerateSampleCsv
 *
 * Used for stock take download sample csv
 */
class GenerateSampleCsv
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Filesystem
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
     * GenerateSampleCsv constructor.
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
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
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR . '/import/' . $name . '.csv';
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
     * Generate stock-take prepare product
     *
     * @return array
     */
    public function generateStockTakePrepareProduct()
    {
        $data = [
            [
                __('SKU')
            ]
        ];
        $firstProduct = $this->collectionFactory->create()->getFirstItem();
        if ($firstProduct->getId()) {
            $data[] = [
                $firstProduct->getSku()
            ];
        }
        return $data;
    }

    /**
     * Generate stock-take counted product
     *
     * @return array
     */
    public function generateStockTakeCountedProduct()
    {
        $data = [
            [
                __('SKU'),
                __('COUNTED QUANTITY'),
                __('REASON OF DIFFERENCE')
            ]
        ];
        $firstProduct = $this->collectionFactory->create()->getFirstItem();
        if ($firstProduct->getId()) {
            $data[] = [
                $firstProduct->getSku(),
                100,
                ''
            ];
        }
        return $data;
    }

    /**
     * Generate stock-take prepare file
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function generateStockTakePrepareFile()
    {
        return $this->createDownloadResponse(
            'import_stocktake_product',
            $this->generateStockTakePrepareProduct()
        );
    }

    /**
     * Generate stock-take counted file
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function generateStockTakeCountedFile()
    {
        return $this->createDownloadResponse(
            'import_stocktake_product',
            $this->generateStockTakeCountedProduct()
        );
    }
}
