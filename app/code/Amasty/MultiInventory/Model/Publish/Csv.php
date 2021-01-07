<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Publish;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Filesystem\File\WriteFactory;

/**
 * Class Csv
 */
class Csv
{
    const ENCLOSE = '"';

    const DELIMETER = ',';

    const PATH = '/var/amasty_export_csv/';

    /**
     * Source file handler.
     *
     * @var Write
     */
    private $fileHandler;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var WriteFactory
     */
    public $file;

    /**
     * @var file
     */
    public $filename;

    /**
     * Publisher constructor.
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\WriteFactory $directory
     * @param WriteFactory $file
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directory,
        WriteFactory $file
    ) {
        $this->filesystem = $filesystem;
        $this->file       = $file;
        $this->directory  = $directory;
    }

    /**
     * @return string
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function getPathFile()
    {
        $dir  = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $path = $dir->getAbsolutePath() . self::PATH;
        if (!$dir->isExist($path)) {
            $directory = $this->directory->create($path);
            $directory->create();
        }

        return $path;
    }

    /**
     * @return file
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function init($filename, $type = 'w')
    {
        $path = $this->getPathFile();
        $file    = $this->file;
        $allPath = $path . $filename;
        $this->setFilename($allPath);
        $this->fileHandler = $file->create(
            $allPath,
            DriverPool::FILE,
            $type
        );
    }

    /**
     * @param array $rowData
     *
     * @return $this
     * @throws FileSystemException
     */
    public function writeRow(array $rowData)
    {
        foreach ($rowData as &$value) {
            $value = str_replace(
                ["\r\n", "\n", "\r"],
                ' ',
                $value
            );
        }
        $this->fileHandler->writeCsv(
            $rowData,
            self::DELIMETER,
            self::ENCLOSE
        );

        return $this;
    }

    /**
     * Desctuct
     */
    public function destruct()
    {
        if (is_object($this->fileHandler)) {
            $this->fileHandler->close();
        }
    }
}
