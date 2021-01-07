<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */

namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseShippingInterface;
use Amasty\MultiInventory\Api\WarehouseShippingRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Shipping as ShippingResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ShippingRepository
 */
class ShippingRepository implements WarehouseShippingRepositoryInterface
{
    /**
     * @var ShippingResource
     */
    protected $resource;

    /**
     * @var ShippingFactory
     */
    protected $factory;

    /**
     * ShippingRepository constructor.
     *
     * @param ShippingResource $resource
     * @param ShippingFactory $factory
     */
    public function __construct(
        ShippingResource $resource,
        ShippingFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseShippingInterface $item)
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
        /** @var WarehouseShippingInterface $model */
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Shipping Method with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseShippingInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the warehouse shipping %1',
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
