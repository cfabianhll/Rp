<?php

/**
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory as FileFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class CsvFileProcessor
 *
 * Csv file processor for stock-take
 */
class CsvFileProcessor
{
    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var WriteFactory
     */
    protected $fileWriteFactory;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CsvFileProcessor constructor.
     *
     * @param Csv $csvProcessor
     * @param Filesystem $filesystem
     * @param WriteFactory $fileWriteFactory
     * @param FileFactory $fileFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Csv $csvProcessor,
        Filesystem $filesystem,
        WriteFactory $fileWriteFactory,
        FileFactory $fileFactory,
        LoggerInterface $logger
    ) {
        $this->csvProcessor = $csvProcessor;
        $this->filesystem = $filesystem;
        $this->fileWriteFactory = $fileWriteFactory;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
    }

    /**
     * Read file
     *
     * @param string $fileName
     * @return array
     * @throws \Exception
     */
    public function readFile(string $fileName)
    {
        return $this->csvProcessor->getData($fileName);
    }

    /**
     * Create file
     *
     * @param string $name
     * @return \Magento\Framework\Filesystem\File\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createFile(string $name)
    {
        $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('import');
        $filename = DirectoryList::VAR_DIR . '/import/' . $name . '.csv';
        return $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->openFile($filename, 'w+');
    }

    /**
     * Create download file
     *
     * @param string $downloadFileName
     * @param string $name
     * @return \Magento\Framework\App\ResponseInterface|void
     * @throws \Exception
     */
    public function createDownloadFile(string $downloadFileName, string $name)
    {
        try {
            return $this->fileFactory->create(
                $downloadFileName.'.csv',
                [
                    'type' => 'filename',
                    'value' => DirectoryList::VAR_DIR . '/import/' . $name . '.csv',
                    'rm' => true  // can delete file after use
                ],
                DirectoryList::VAR_DIR
            );
        } catch (LocalizedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
