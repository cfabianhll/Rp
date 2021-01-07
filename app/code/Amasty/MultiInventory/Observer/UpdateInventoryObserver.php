<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Model\ConfigurableProductIdsReindexRegistry;
use Amasty\MultiInventory\Model\Warehouse;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class UpdateInventoryObserver implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $stockProcessor;

    /**
     * @var \Amasty\MultiInventory\Model\ConfigurableProductIdsReindexRegistry
     */
    private $idsReindexRegistry;

    /**
     * UpdateInventoryObserver constructor.
     *
     * @param Processor $stockProcessor
     * @param ConfigurableProductIdsReindexRegistry $idsReindexRegistry
     */
    public function __construct(
        Processor $stockProcessor,
        ConfigurableProductIdsReindexRegistry $idsReindexRegistry
    ) {
        $this->stockProcessor = $stockProcessor;
        $this->idsReindexRegistry = $idsReindexRegistry;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getEvent()->getObject();
        $productIds = $object->getRegisteredEntity(Warehouse::CACHE_TAG);

        if (count($productIds)) {
            $forceReindex = false;
            //if simple of configurable becomes out of stock\changes to in stock
            //and product reindex set as update on schedule
            //we must force reindex configurable product to display correct options
            if ($configurableIds = $this->idsReindexRegistry->getIdsToReindex()) {
                $forceReindex = true;
                $productIds = array_merge($productIds, $configurableIds);
            }
            $this->stockProcessor->reindexList($productIds, $forceReindex);
        } else {
            $this->stockProcessor->reindexAll();
        }

        return $this;
    }
}
