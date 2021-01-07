<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-optimize
 * @version   1.0.5
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\OptimizeImage\Service;

use Magento\Framework\Filesystem;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Model\Config;
use Mirasvit\OptimizeImage\Repository\FileRepository;

class FileListBatchService
{
    private $config;

    private $fileRepository;

    private $fs;

    public function __construct(
        Config $config,
        FileRepository $fileRepository,
        Filesystem $fs
    ) {
        $this->config         = $config;
        $this->fileRepository = $fileRepository;
        $this->fs             = $fs;
    }

    public function getSize()
    {
        return $this->getUnprocessedCollection()->getSize();
    }

    /**
     * @param int $batchSize
     *
     * @return FileInterface[]|false
     */
    public function getBatch($batchSize = 100)
    {
        $collection = $this->getUnprocessedCollection();
        $collection->setPageSize($batchSize);

        if ($collection->count() === 0) {
            return false;
        }

        return $collection;
    }

    public function getUnprocessedCollection()
    {
        $collection = $this->fileRepository->getCollection();

        $collection->addFieldToFilter([
            FileInterface::ACTUAL_SIZE, FileInterface::PROCESSED_AT,
        ], [
            ['null' => true],
            ['lteq' => date('Y-m-d H:i:s', time() - 365 * 24 * 60 * 60),],
        ])->setOrder(FileInterface::ID);

        return $collection;
    }
}
