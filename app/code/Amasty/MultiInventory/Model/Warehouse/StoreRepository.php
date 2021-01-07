<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseStoreInterface;
use Amasty\MultiInventory\Api\WarehouseStoreRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Store as StoreResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class StoreRepository
 */
class StoreRepository implements WarehouseStoreRepositoryInterface
{
    /**
     * @var StoreResource
     */
    protected $resource;

    /**
     * @var CustomerGroupFactory|StoreFactory
     */
    protected $factory;

    /**
     * WarehouseStoreRepository constructor.
     *
     * @param StoreResource $resource
     * @param StoreFactory $factory
     */
    public function __construct(
        StoreResource $resource,
        StoreFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseStoreInterface $item)
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
        /** @var WarehouseStoreInterface $model */
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Store with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseStoreInterface $item)
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
