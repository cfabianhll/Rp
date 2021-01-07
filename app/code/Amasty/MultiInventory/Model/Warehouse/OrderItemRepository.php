<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseOrderItemInterface;
use Amasty\MultiInventory\Api\WarehouseOrderItemRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item as OrderItemResource;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Order\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class OrderItemRepository
 */
class OrderItemRepository implements WarehouseOrderItemRepositoryInterface
{
    /**
     * @var OrderItemResource
     */
    protected $resource;

    /**
     * @var Order\ItemFactory
     */
    protected $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * OrderItemRepository constructor.
     *
     * @param OrderItemResource $resource
     * @param ItemFactory $factory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        OrderItemResource $resource,
        ItemFactory $factory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseOrderItemInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        /** @var WarehouseOrderItemInterface $model */
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Order Item with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderItemId($orderItemId)
    {
        $model = $this->collectionFactory->create()
            ->addFieldToFilter(WarehouseOrderItemInterface::ORDER_ITEM_ID, $orderItemId)
            ->getFirstItem();

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseOrderItemInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the warehouse store: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
