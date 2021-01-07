<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml;

use Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Logger\Logger;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Action\Full;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\Publish\Csv;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Class AbstractImport
 */
class AbstractImport extends Action
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
     * @var WarehouseItemRepositoryInterface
     */
    private $repository;

    /**
     * @var WarehouseImportRepositoryInterface
     */
    private $importRepository;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ItemFactory
     */
    private $factory;

    /**
     * @var Csv
     */
    public $csv;

    /**
     * @var Timezone
     */
    public $timezone;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Full
     */
    private $fullReindexAction;

    /**
     * AbstractImport constructor.
     *
     * @param Context                                                       $context
     * @param Filter                                                        $filter
     * @param CollectionFactory                                             $collectionFactory
     * @param WarehouseItemRepositoryInterface   $repository
     * @param WarehouseImportRepositoryInterface $importRepository
     * @param ItemFactory            $factory
     * @param Processor      $processor
     * @param Csv                      $csv
     * @param Timezone                   $timezone
     * @param Logger                          $logger
     * @param Full    $fullReindexAction
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        WarehouseItemRepositoryInterface $repository,
        WarehouseImportRepositoryInterface $importRepository,
        ItemFactory $factory,
        Processor $processor,
        Csv $csv,
        Timezone $timezone,
        Logger $logger,
        Full $fullReindexAction
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->repository = $repository;
        $this->processor = $processor;
        $this->factory = $factory;
        $this->csv = $csv;
        $this->timezone = $timezone;
        $this->importRepository = $importRepository;
        $this->logger = $logger;
        $this->fullReindexAction = $fullReindexAction;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $this->import();
    }

    /**
     * Import
     */
    public function import()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collection->addFieldToFilter('import_number', $this->getRequest()->getParam('form_key'));
        $collectionSize = $collection->getSize();
        foreach ($collection as $stock) {
            try {
                $item = $this->repository->getByProductWarehouse($stock->getProductId(), $stock->getWarehouseId());
                $oldQty = $item->getQty();
                $item->setProductId($stock->getProductId());
                $item->setWarehouseId($stock->getWarehouseId());
                $item->setQty($stock->getNewQty());
                $item->recalcAvailable();
                $this->repository->save($item);
                $this->logger->infoWh(
                    $item->getProduct()->getSku(),
                    $item->getProductId(),
                    $item->getWarehouse()->getTitle(),
                    $item->getWarehouse()->getCode(),
                    $oldQty,
                    $item->getQty(),
                    'null',
                    'Import'
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->fullReindexAction->setUpdateStockStatus(true);
        $this->processor->reindexAll();
        $this->remove();
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been imported.', $collectionSize)
        );
    }

    /**
     * Remove
     */
    private function remove()
    {
        $importNumber = $this->filter->getImportNumber();

        $collection = $this->collectionFactory->create()->addFieldToFilter('import_number', $importNumber);
        foreach ($collection as $item) {
            $this->importRepository->delete($item);
        }
    }
}
