<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Export;

use Amasty\MultiInventory\Api\ExportRepositoryInterface;
use Amasty\MultiInventory\Model\Export;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;

/**
 * Class Delete
 */
class Delete extends \Amasty\MultiInventory\Controller\Adminhtml\Export
{
    /**
     * @var ExportRepositoryInterface
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
     * Delete constructor.
     * @param Action\Context $context
     * @param ExportRepositoryInterface $repository
     * @param Filesystem $fileSystem
     * @param File $file
     */
    public function __construct(
        Action\Context $context,
        ExportRepositoryInterface $repository,
        Filesystem $fileSystem,
        File $file
    ) {
        $this->repository = $repository;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('export_id');
        if ($id) {
            try {
                $export = $this->repository->getById($id);
                $filename = $export->getFile();
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the export file.'));
                $path    = $this->fileSystem
                    ->getDirectoryWrite(DirectoryList::MEDIA)
                    ->getAbsolutePath('/') . Export::PATH_EXPORT . $filename;
                if ($this->file->fileExists($path)) {
                    $this->file->rm($path);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/index');
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find a export to delete.'));

        return $resultRedirect->setPath('*/*/');
    }
}
