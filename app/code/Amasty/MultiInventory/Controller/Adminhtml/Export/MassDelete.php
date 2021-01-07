<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Export;

use Amasty\MultiInventory\Api\ExportRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\Export;
use Amasty\MultiInventory\Model\ResourceModel\Export\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 */
class MassDelete extends \Amasty\MultiInventory\Controller\Adminhtml\Export
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var File
     */
    private $file;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ExportRepositoryInterface $repository
     * @param Filesystem $fileSystem
     * @param File $file
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ExportRepositoryInterface $repository,
        Filesystem $fileSystem,
        File $file
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $export) {
            $filename = $export->getFile();
            $this->repository->delete($export);
            $path    = $this->fileSystem
                    ->getDirectoryWrite(DirectoryList::MEDIA)
                    ->getAbsolutePath('/') . Export::PATH_EXPORT . $filename;
            if ($this->file->fileExists($path)) {
                $this->file->rm($path);
            }
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
