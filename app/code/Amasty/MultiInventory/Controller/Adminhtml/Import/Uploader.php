<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


/**
 *
 *
 *
 */
namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Controller\Adminhtml\Import;
use Amasty\MultiInventory\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Uploader
 */
class Uploader extends Import
{

    const PATH = 'amasty_multiinventory/import';

    const MIME_OCTET_STREAM = 'application/octet-stream';

    const MIME_CSV = 'text/csv';

    const MIME_CSV_MS_EXCEL = 'application/vnd.ms-excel';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Uploader constructor.
     * @param Action\Context $context
     * @param Data $helper
     * @param Filesystem $filesystem
     * @param EncoderInterface $jsonEncoder
     * @param RawFactory $resultRawFactory
     * @param UploaderFactory $uploaderFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        Data $helper,
        Filesystem $filesystem,
        EncoderInterface $jsonEncoder,
        RawFactory $resultRawFactory,
        UploaderFactory $uploaderFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->fileSystem = $filesystem;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Upload file via ajax
     */
    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'import_file']);
            $uploader->setAllowedExtensions(['csv', 'xml']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $result = $uploader->save($mediaDirectory->getAbsolutePath(self::PATH));
            unset($result['tmp_name']);
            if ($result['type'] == self::MIME_OCTET_STREAM || $result["type"] == self::MIME_CSV_MS_EXCEL) {
                $result['type'] = self::MIME_CSV;
            }
            $result['url'] = $this->getTmpMediaUrl($result['file']);
            $result['message'] = __('The import file has been uploaded successfully. '
                . 'Please click "Import" to continue import.');
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        }
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getBaseTmpMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::PATH;
    }

    /**
     * @param $file
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getTmpMediaUrl($file)
    {
        return $this->getBaseTmpMediaUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * @param $file
     * @return string
     */
    private function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
