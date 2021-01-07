<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface;
use Amasty\MultiInventory\Controller\Adminhtml\Import;
use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Model\Warehouse\ImportFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Clear
 */
class Clear extends Import
{

    const PATH = 'amasty_multiinventory/import';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var ImportFactory
     */
    private $importFactory;

    /**
     * @var WarehouseImportRepositoryInterface
     */
    private $importRepository;

    /**
     * Send constructor.
     * @param Action\Context $context
     * @param Data $helper
     * @param EncoderInterface $jsonEncoder
     * @param RawFactory $resultRawFactory
     * @param ImportFactory $importFactory
     * @param WarehouseImportRepositoryInterface $importRepository
     */
    public function __construct(
        Action\Context $context,
        Data $helper,
        EncoderInterface $jsonEncoder,
        RawFactory $resultRawFactory,
        ImportFactory $importFactory,
        WarehouseImportRepositoryInterface $importRepository
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->importFactory = $importFactory;
        $this->importRepository = $importRepository;
    }

    /**
     * Run
     */
    public function execute()
    {
        $number = $this->getRequest()->getParam('number');
        try {
            $collection = $this->importFactory->create()->getCollection()
                ->addFieldToFilter('import_number', $number);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $this->importRepository->delete($item);
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
