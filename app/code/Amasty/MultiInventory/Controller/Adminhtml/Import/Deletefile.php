<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Controller\Adminhtml\Import;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Deletefile
 */
class Deletefile extends Import
{
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
     * Deletefile constructor.
     * @param Action\Context $context
     * @param File $filesystem
     * @param EncoderInterface $jsonEncoder
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Action\Context $context,
        File $filesystem,
        EncoderInterface $jsonEncoder,
        RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
        $this->fileSystem = $filesystem;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Delete file from server
     */
    public function execute()
    {
        $path = $this->getRequest()->getParam('path');
        try {
            if ($path) {
                if ($this->fileSystem->fileExists($path)) {
                    $this->fileSystem->rm($path);
                }
            }
            $result = ['response' => true];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        }
    }
}
