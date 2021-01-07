<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Helper;

use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface;
use Amasty\MultiInventory\Logger\Logger;
use Amasty\MultiInventory\Model\Dispatch;
use Amasty\MultiInventory\Model\EmailNotification;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\Collection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\File\Size;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var System
     */
    private $system;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var WarehouseOrderItemRepositoryInterface
     */
    private $repository;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var EmailNotification
     */
    private $emailNotification;

    /**
     * @var Size
     */
    private $fileSize;

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var CollectionFactory
     */
    private $quoteItemWhCollection;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $warehouseStockRepository;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * Data constructor.
     *
     * @param WarehouseFactory $factory
     * @param Dispatch $dispatch
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param WarehouseOrderItemRepositoryInterface $repository
     * @param WarehouseItemRepositoryInterface $itemRepository
     * @param CollectionFactory $quoteItemWhCollection
     * @param Processor $processor
     * @param EmailNotification $emailNotification
     * @param Size $fileSize
     * @param Logger $logger
     * @param ProductRepositoryInterface $productRepository
     * @param WarehouseItemRepositoryInterface $warehouseStockRepository
     * @param DataObjectFactory $objectFactory
     * @param System $system
     * @param Context $context
     */
    public function __construct(
        WarehouseFactory $factory,
        Dispatch $dispatch,
        OrderItemRepositoryInterface $orderItemRepository,
        WarehouseOrderItemRepositoryInterface $repository,
        WarehouseItemRepositoryInterface $itemRepository,
        CollectionFactory $quoteItemWhCollection,
        Processor $processor,
        EmailNotification $emailNotification,
        Size $fileSize,
        Logger $logger,
        ProductRepositoryInterface $productRepository,
        WarehouseItemRepositoryInterface $warehouseStockRepository,
        DataObjectFactory $objectFactory,
        System $system,
        Context $context
    ) {
        parent::__construct($context);
        $this->factory = $factory;
        $this->system = $system;
        $this->dispatch = $dispatch;
        $this->repository = $repository;
        $this->itemRepository = $itemRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->processor = $processor;
        $this->emailNotification = $emailNotification;
        $this->fileSize = $fileSize;
        $this->logger = $logger;
        $this->quoteItemWhCollection = $quoteItemWhCollection;
        $this->productRepository = $productRepository;
        $this->warehouseStockRepository = $warehouseStockRepository;
        $this->objectFactory = $objectFactory;
    }

    /**
     * @return int
     */
    public function getDefaultId()
    {
        return $this->factory->create()->getDefaultId();
    }

    /**
     * @return Dispatch
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @param Order $order
     *
     * @return array
     */
    public function dispatchWarehouseForQuote($order)
    {
        $result = [];
        /** @var Collection $quoteWhItems */
        $quoteWhItems = $this->quoteItemWhCollection->create()
            ->getWarehousesFromQuote($order->getQuoteId());

        foreach ($order->getAllItems() as $item) {
            if ($item->getParentItemId() || $this->isSimple($item)) {
                $warehouseQuote = $quoteWhItems->getItemByColumnValue('quote_item_id', $item->getQuoteItemId());

                if ($warehouseQuote !== null) {
                    $result[] = $this->getArrayItem($item, $warehouseQuote->getWarehouseId());
                }
            }
        }

        return $result;
    }

    /**
     * Call Dispatches from Config
     *
     * @param OrderItemInterface $item
     *
     * @return array
     * @throws \Zend_Json_Exception
     */
    public function calcDispatch($item)
    {
        $this->dispatch->setOrderItem($item);
        $this->dispatch->setDirection(Dispatch::DIRECTION_ORDER);
        $this->dispatch->searchWh();

        return $this->getArrayItem($item, $this->dispatch->getWarehouse());
    }

    /**
     * @param OrderItemInterface $item
     * @param int $warehouse
     * @return array
     */
    public function getArrayItem($item, $warehouse)
    {
        return [
            'order_id' => $item->getOrderId(),
            'order_item_id' => $item->getId(),
            'warehouse_id' => $warehouse,
            'product_id' => $item->getProductId(),
            'qty' => $item->getQtyOrdered()
        ];
    }

    /**
     * change Qty
     *
     * @param \Amasty\MultiInventory\Model\Warehouse\Order\Item $item
     * @param $entity
     * @param $event
     * @param int $ship
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setShip(\Amasty\MultiInventory\Model\Warehouse\Order\Item $item, $entity, $event, $ship = 0)
    {
        $fields = $item->toArray();

        if ($entity) {
            $fields['qty_ordered'] = $this->getQtyToShip($entity, $fields);
            $itemId = ($fields['parent_item_id']) ? $fields['parent_item_id'] : $fields['order_item_id'];
            $isBundleProduct = $this->checkForBundle($fields, $entity);
            $itemId = $isBundleProduct ? $fields['order_item_id'] : $itemId;

            if (isset($entity['warehouse'][$itemId])) {
                $warehouse = $this->getWarehouse($entity, $itemId);

                if ($warehouse != 0 && $warehouse != $fields['warehouse_id']) {
                    $this->changeWarehouse($fields, $warehouse, $ship);
                }
            } else {
                $warehouse = $fields['warehouse_id'];
            }
        } else {
            $warehouse = $fields['warehouse_id'];
        }

        if (!$ship) {
            $this->setQty($fields, $warehouse, $event);
        }
    }

    /**
     * @param $fields
     * @param $entity
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkForBundle($fields, $entity)
    {
        if (isset($fields['parent_item_id'])) {
            $parentItemId = $fields['parent_item_id'];

            foreach ($entity->getItems() as $item) {
                if ($item->getData('order_item_id') === $parentItemId) {
                    $productType = $this->productRepository->getById($item->getProductId())->getTypeId();

                    if ($productType === 'bundle') {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Return Qty
     *
     * @param \Amasty\MultiInventory\Model\Warehouse\Order\Item $item
     * @param AbstractModel $entity
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function setReturn($item, $entity)
    {
        $fields = $item->toArray();
        $fields['qty_ordered'] = $this->getQtyToReturn($entity, $fields);
        $warehouse = $fields['warehouse_id'];
        $this->setReturnQty($fields, $warehouse);
    }

    /**
     * change manual Warehouse
     *
     * @param $fields
     * @param $warehouse
     * @param $ship
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function changeWarehouse($fields, $warehouse, $ship)
    {
        $orderItem = $this->repository->getById($fields['item_id']);
        $stockItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $fields['warehouse_id']);
        $oldQty = $stockItem->getQty();

        if (!$ship) {
            $stockItem->setShipQty($stockItem->getShipQty() - $fields['qty_ordered']);
        } else {
            $stockItem->setQty($stockItem->getQty() + $fields['qty_ordered']);
        }
        $stockItem->recalcAvailable();
        $this->itemRepository->save($stockItem);

        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                'change warehouse',
                'null',
                'true'
            );
        }
        $newItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $warehouse);
        $oldQty = $newItem->getQty();

        if (!$ship) {
            $newItem->setShipQty($newItem->getShipQty() + $fields['qty_ordered']);
        } else {
            $newItem->setQty($newItem->getQty() - $fields['qty_ordered']);
        }
        $newItem->recalcAvailable();
        $this->itemRepository->save($newItem);

        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $newItem->getProduct()->getSku(),
                $newItem->getProductId(),
                $newItem->getWarehouse()->getTitle(),
                $newItem->getWarehouse()->getCode(),
                $oldQty,
                $newItem->getQty(),
                'change warehouse',
                'null',
                'true'
            );
        }
        $orderItem->setWarehouseId($warehouse);
        $this->repository->save($orderItem);
        $this->processor->reindexRow($fields['product_id']);
    }

    /**
     * Set Qty
     *
     * @param array $fields
     * @param int $warehouseId
     * @param $event
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setQty($fields, $warehouseId, $event)
    {
        $stockItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $warehouseId);
        $oldQty = $stockItem->getQty();
        $stockItem->setQty($stockItem->getQty() - $fields['qty_ordered']);
        $stockItem->setShipQty($stockItem->getShipQty() - $fields['qty_ordered']);
        $stockItem->recalcAvailable();
        $this->itemRepository->save($stockItem);

        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                $event
            );
        }
        $this->processor->reindexRow($fields['product_id']);
        $this->checkLowStock($stockItem);
    }

    /**
     * @param array $fields
     * @param int $warehouseId
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function setReturnQty($fields, $warehouseId)
    {
        $stockItem = $this->itemRepository->getByProductWarehouse($fields['product_id'], $warehouseId);
        $oldQty = $stockItem->getQty();

        $orderItem = $this->orderItemRepository->get($fields['order_item_id']);
        $stockItem->setQty($stockItem->getQty() + $fields['qty_ordered']);

        if ($this->system->getPhysicalDecreese() == SYSTEM::ORDER_SHIPMENT) {
            $qtyRefundShippedDiff = $fields['qty_ordered'] - $orderItem->getQtyShipped();

            if ($qtyRefundShippedDiff > 0) {
                $stockItem->setQty($stockItem->getQty() - $qtyRefundShippedDiff);
                $stockItem->setShipQty($stockItem->getShipQty() - $qtyRefundShippedDiff);
            }
        }

        $stockItem->recalcAvailable();
        $this->itemRepository->save($stockItem);

        if ($this->system->isEnableLog()) {
            $this->logger->infoWh(
                $stockItem->getProduct()->getSku(),
                $stockItem->getProductId(),
                $stockItem->getWarehouse()->getTitle(),
                $stockItem->getWarehouse()->getCode(),
                $oldQty,
                $stockItem->getQty(),
                'creditmemo'
            );
        }
        $this->processor->reindexRow($fields['product_id']);
    }

    /**
     * @param $entity
     * @param $field
     *
     * @return int
     */
    private function getWarehouse($entity, $field)
    {
        foreach ($entity['warehouse'] as $key => $record) {
            if ($key == $field) {
                return $record;
            }
        }

        return 0;
    }

    /**
     * @param $entity
     * @param $field
     *
     * @return mixed
     */
    private function getQtyToShip($entity, $field)
    {
        $itemId = $field['parent_item_id'] ?: $field['order_item_id'];

        foreach ($entity['items'] as $record) {
            if ($record->getOrderItemId() == $itemId) {
                return $record->getQty();
            }
        }

        return $field['qty_ordered'];
    }

    /**
     * @param $entity
     * @param $field
     *
     * @return mixed
     */
    private function getQtyToReturn($entity, $field)
    {
        $itemId = $field['parent_item_id'] ?: $field['order_item_id'];

        foreach ($entity['items'] as $record) {
            if ($record->getOrderItemId() == $itemId) {
                return $record->getQty();
            }
        }

        return 0;
    }

    /**
     * check for Low warehouses
     *
     * @param Item $item
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkLowStock(Item $item)
    {
        $qty = $item->getAvailableQty();

        if ($qty <= $this->system->getLowStock()) {
            $this->emailNotification->sendLowStock($item->getProductId(), $item->getWarehouseId());
        }
    }

    /**
     * @param Order $order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setOrderEmail($order)
    {
        $this->emailNotification->setNewOrder($order);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item|\Magento\Quote\Model\Quote\Item $item
     *
     * @return bool
     */
    public function isSimple($item)
    {
        return $item->getProduct()->getTypeId() == Type::TYPE_SIMPLE;
    }

    /**
     * @return Phrase
     */
    public function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->fileSize->getFileSizeInMb($this->getMaxSizeFile());

        if ($maxImageSize) {
            $message = __('Make sure your file isn\'t more than %1 MB.', $maxImageSize);
        } else {
            $message = __('We can\'t provide the upload settings right now.');
        }

        return $message;
    }

    /**
     * @return float
     */
    public function getMaxSizeFile()
    {
        return $this->fileSize->getMaxFileSize();
    }

    /**
     * @param int $productId
     *
     * @return Item
     * @throws \Zend_Json_Exception
     */
    public function getStock($productId)
    {
        if (!$this->system->isLockOnStore()) {
            return $this->warehouseStockRepository->getByProductWarehouse(
                $productId,
                $this->dispatch->getDefaultWarehouseId()
            );
        }
        $data = $this->objectFactory->create(
            [
                'data' => [
                    'product_id' => $productId
                ]
            ]
        );
        $this->dispatch->setCallables($this->system->getDispatchOrder());
        $this->dispatch->setData('object', $data);
        $this->dispatch->setDirection(Dispatch::DIRECTION_STORE);
        $this->dispatch->searchWh();
        $warehouse = $this->dispatch->getWarehouse();

        return $this->warehouseStockRepository->getByProductWarehouse($productId, $warehouse);
    }
}
