<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Observer;

use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface;
use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory;
use Amasty\MultiInventory\Model\Warehouse\Order\Processor;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class CheckoutAllSubmitAfterObserver implements ObserverInterface
{
    /**
     * @var ItemFactory
     */
    private $orderItemFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var System
     */
    private $system;

    /**
     * @var WarehouseOrderItemRepositoryInterface
     */
    private $repository;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CollectionFactory
     */
    protected $stockCollection;

    /**
     * @var Processor
     */
    private $orderProcessor;

    /**
     * CheckoutAllSubmitAfterObserver constructor.
     *
     * @param ItemFactory $orderItemFactory
     * @param CollectionFactory $stockCollection
     * @param WarehouseOrderItemRepositoryInterface $repository
     * @param WarehouseItemRepositoryInterface $itemRepository
     * @param Data $helper
     * @param System $system
     * @param ManagerInterface $messageManager
     * @param \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor
     * @param OrderSender $orderSender
     * @param Session $checkoutSession
     * @param Registry $registry
     * @param Processor $orderProcessor
     */
    public function __construct(
        ItemFactory $orderItemFactory,
        CollectionFactory $stockCollection,
        WarehouseOrderItemRepositoryInterface $repository,
        WarehouseItemRepositoryInterface $itemRepository,
        Data $helper,
        System $system,
        ManagerInterface $messageManager,
        \Amasty\MultiInventory\Model\Indexer\Warehouse\Processor $processor,
        OrderSender $orderSender,
        Session $checkoutSession,
        Registry $registry,
        Processor $orderProcessor
    ) {
        $this->orderItemFactory = $orderItemFactory;
        $this->stockCollection  = $stockCollection;
        $this->helper           = $helper;
        $this->system           = $system;
        $this->repository       = $repository;
        $this->processor        = $processor;
        $this->itemRepository   = $itemRepository;
        $this->messageManager   = $messageManager;
        $this->orderSender      = $orderSender;
        $this->checkoutSession  = $checkoutSession;
        $this->registry         = $registry;
        $this->orderProcessor   = $orderProcessor;
    }

    /**
     * @param EventObserver $observer
     *
     * @return $this|void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->system->isMultiEnabled()) {
            return $this;
        }

        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order) {
            /** @var Order[] $orders */
            $orders = $observer->getEvent()->getOrders(); //in case if multishipping method is used
        } else {
            $orders[] = $order;
        }

        if (empty($orders)) {
            return $this;
        }

        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        $quote->setInventoryProcessed(true);

        foreach ($orders as $order) {
            $this->processOrder($order, $quote);
        }

        return $this;
    }

    /**
     * @param Order $order
     * @param Quote $quote
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function processOrder($order, $quote)
    {
        list($result, $orders) = $this->dispatchOrderByWarehouses($order);

        foreach ($result as $itemWrapper) {
            $this->convertToOrderItem($itemWrapper);
            $this->modifyWarehouseStock($itemWrapper);
        }

        if ($this->system->getPhysicalDecreese() == System::ORDER_CREATION
            && $this->system->getAvailableDecreese()
            && $order->getId()
        ) {
            $entity = [];
            $collection = $this->orderItemFactory->create()->getCollection()->getOrderItemInfo($order->getId());

            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $this->helper->setShip($item, $entity, 'order', 0);
                }
            }
        }
        $this->sendOrders($orders, $quote);
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    private function dispatchOrderByWarehouses($order)
    {
        $result = [];
        $orders = [$order];

        try {
            if ($this->system->getDefinationWarehouse()) {
                $result = $this->helper->dispatchWarehouseForQuote($order);
            } else {
                $result = $this->orderProcessor->dispatchWarehouse($order);
            }
            list($result, $orders) = $this->orderProcessor->separateOrders($result, $order);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the order for warehouse now.'));
        }

        return [$result, $orders];
    }

    /**
     * @param array $item
     *
     * @return $this
     * @throws CouldNotSaveException
     */
    private function convertToOrderItem($item)
    {
        $orderItem = $this->orderItemFactory->create();
        unset($item['qty']);
        unset($item['product_id']);
        $orderItem->setData($item);
        $this->repository->save($orderItem);
        return $this;
    }

    /**
     * @param array $itemWrapper
     *
     * @return Item
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws MailException
     */
    private function modifyWarehouseStock($itemWrapper)
    {
        $productId = $itemWrapper['product_id'];
        /** @var Item $warehouseStock */
        $warehouseStock = $this->itemRepository->getByProductWarehouse($productId, $itemWrapper['warehouse_id']);
        $warehouseStock->setShipQty($warehouseStock->getShipQty() + $itemWrapper['qty']);
        $warehouseStock->recalcAvailable();
        $this->itemRepository->save($warehouseStock);
        $this->processor->reindexRow($productId);

        if ($this->system->getPhysicalDecreese() != System::ORDER_CREATION) {
            $this->helper->checkLowStock($warehouseStock);
        }

        return $warehouseStock;
    }

    /**
     * @param array $orders
     * @param Quote $quote
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws MailException
     */
    private function sendOrders($orders, $quote)
    {
        $orderIds = [];
        /** @var Order $order */
        foreach ($orders as $order) {
            $orderIds[] = $order->getIncrementId();
            $order->setItems(null);

            if ($this->isCanSendEmail($quote)) {
                $this->orderSender->send($order);
            }
            $this->helper->setOrderEmail($order);
        }

        if (count($orderIds) > 1) {
            $this->checkoutSession->setSeparetedOrderIds($orderIds);
        }
    }

    /**
     * @param Quote $quote
     *
     * @return bool
     */
    private function isCanSendEmail($quote)
    {
        return !(bool)$quote->getPayment()->getOrderPlaceRedirectUrl()
        && !$this->registry->registry('multiinventory_cant_send_new_email')
        && $quote->getCustomerNoteNotify() === true;
    }
}
