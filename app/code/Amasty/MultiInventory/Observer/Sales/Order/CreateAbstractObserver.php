<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer\Sales\Order;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System as HelperSystem;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Order\Item;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Class CreateAbstractObserver
 */
abstract class CreateAbstractObserver implements ObserverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var HelperSystem
     */
    protected $system;

    /**
     * CreateAbstractObserver constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Data                                                $helper
     * @param HelperSystem                                                                      $system
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $helper,
        HelperSystem $system
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->system = $system;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if ($this->isCanExecute()) {
            /** @var AbstractModel $entity */
            $entity = $observer->getEvent()->getDataObject();
            if (!is_object($entity) || !$entity->getOrderId()) {
                return;
            }

            $collection = $this->collectionFactory->create()->getOrderItemInfo($entity->getOrderId());
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $this->processItem($item, $entity);
                }
            }
        }
    }

    /**
     * @param Item $item
     * @param AbstractModel $entity
     */
    protected function processItem($item, $entity)
    {
        $this->helper->setShip($item, $entity, $this->getEventName(), $this->isShip());
    }

    /**
     * @return bool
     */
    protected function isCanExecute()
    {
        return $this->system->isMultiEnabled();
    }

    /**
     * @return bool
     */
    protected function isShip()
    {
        return false;
    }

    /**
     * @return string
     */
    protected function getEventName()
    {
        return '';
    }
}
