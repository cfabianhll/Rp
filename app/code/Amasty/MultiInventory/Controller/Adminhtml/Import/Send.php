<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Import;

use Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Controller\Adminhtml\Import;
use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Model\Warehouse\ImportFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Json\EncoderInterface;

/**
 * Class Send
 */
class Send extends Import
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
     * @var WarehouseItemRepositoryInterface
     */
    private $stockRepository;

    /**
     * Send constructor.
     *
     * @param Action\Context $context
     * @param Data $helper
     * @param EncoderInterface $jsonEncoder
     * @param RawFactory $resultRawFactory
     * @param ImportFactory $importFactory
     * @param WarehouseImportRepositoryInterface $importRepository
     * @param WarehouseItemRepositoryInterface $stockRepository
     */
    public function __construct(
        Action\Context $context,
        Data $helper,
        EncoderInterface $jsonEncoder,
        RawFactory $resultRawFactory,
        ImportFactory $importFactory,
        WarehouseImportRepositoryInterface $importRepository,
        WarehouseItemRepositoryInterface $stockRepository
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->resultRawFactory = $resultRawFactory;
        $this->importFactory = $importFactory;
        $this->importRepository = $importRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Send data from file to DB
     */
    public function execute()
    {
        $imports = $this->getRequest()->getParam('import');

        try {
            foreach ($imports as $import) {
                $newImport = $this->importFactory->create();

                $oldQty = 0;
                $stockItem = $this->stockRepository
                    ->getByProductWarehouse($import['product_id'], $import['warehouse_id']);

                if ($stockItem->getId()) {
                    $oldQty = $stockItem->getQty();
                }
                $import['new_qty'] = $this->setOperations($import['qty'], $oldQty);
                $import['qty'] = $oldQty;
                $newImport->setData($import);

                $this->importRepository->save($newImport);
            }
            $result = ['response' => true];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            $this->getResponse()->setBody($this->jsonEncoder->encode($result));
        }
    }

    /**
     * @param $value
     * @param $oldQty
     * @return int
     */
    private function setOperations($value, $oldQty)
    {
        $newQty = 0;
        if (strpos($value, "-") !== false) {
            $newQty = $oldQty - (int)substr($value, 1);
        } elseif (strpos($value, "+") !== false) {
            $newQty = $oldQty + (int)substr($value, 1);
        } else {
            $newQty = $value;
        }

        return $newQty;
    }
}
