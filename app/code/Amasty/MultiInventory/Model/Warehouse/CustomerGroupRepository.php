<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseCustomerGroupInterface;
use Amasty\MultiInventory\Api\WarehouseCustomerGroupRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CustomerGroup as CustomerGroupResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CustomerGroupRepository
 */
class CustomerGroupRepository implements WarehouseCustomerGroupRepositoryInterface
{
    /**
     * @var CustomerGroupResource
     */
    protected $resource;

    /**
     * @var CustomerGroupFactory
     */
    protected $factory;

    /**
     * WarehouseCustomerGroupRepository constructor.
     *
     * @param CustomerGroupResource $resource
     * @param CustomerGroupFactory $factory
     */
    public function __construct(
        CustomerGroupResource $resource,
        CustomerGroupFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseCustomerGroupInterface $item)
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
        /** @var WarehouseCustomerGroupInterface $model */
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Customer Group with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseCustomerGroupInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the warehouse customer group: %1',
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
