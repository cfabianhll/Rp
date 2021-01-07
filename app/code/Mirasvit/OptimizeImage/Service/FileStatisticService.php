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

use Magento\Framework\App\ResourceConnection;
use Mirasvit\OptimizeImage\Api\Data\FileInterface;
use Mirasvit\OptimizeImage\Repository\FileRepository;

class FileStatisticService
{
    private $fileRepository;

    private $resource;

    public function __construct(
        FileRepository $fileRepository,
        ResourceConnection $resource
    ) {
        $this->fileRepository = $fileRepository;
        $this->resource       = $resource;
    }

    public function getTotalFiles()
    {
        return $this->fileRepository->getCollection()->getSize();
    }

    public function getProcessedFiles()
    {
        $collection = $this->fileRepository->getCollection();
        $collection->addFieldToFilter(FileInterface::ACTUAL_SIZE, ['notnull' => true]);

        return $collection->getSize();
    }

    public function getWebpFiles()
    {
        $collection = $this->fileRepository->getCollection();
        $collection->addFieldToFilter(FileInterface::WEBP_PATH, ['notnull' => true]);

        return $collection->getSize();
    }

    public function getProcessedSize()
    {
        $select = $this->resource->getConnection()->select();
        $select->from($this->resource->getTableName(FileInterface::TABLE_NAME), [])
            ->columns(new \Zend_Db_Expr('SUM(' . FileInterface::ORIGINAL_SIZE . ')'))
            ->where(FileInterface::ACTUAL_SIZE . ' IS NOT NULL');

        $size = $this->resource->getConnection()->fetchOne($select);

        return $size;
    }

    public function getSavedSize()
    {
        $select = $this->resource->getConnection()->select();
        $select->from($this->resource->getTableName(FileInterface::TABLE_NAME), [])
            ->columns(new \Zend_Db_Expr('SUM(original_size - actual_size)'))
            ->where(FileInterface::ACTUAL_SIZE . ' IS NOT NULL');

        $size = $this->resource->getConnection()->fetchOne($select);

        return $size;
    }
}
