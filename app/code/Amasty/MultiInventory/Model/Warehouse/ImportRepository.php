<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseImportInterface;
use Amasty\MultiInventory\Api\WarehouseImportRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Import as ImportResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ImportRepository
 */
class ImportRepository implements WarehouseImportRepositoryInterface
{
    /**
     * @var ImportResource
     */
    protected $resource;

    /**
     * @var ImportFactory
     */
    protected $factory;

    /**
     * ImportRepository constructor.
     *
     * @param ImportResource $resource
     * @param ImportFactory $factory
     */
    public function __construct(
        ImportResource $resource,
        ImportFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseImportInterface $item)
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
        /** @var WarehouseImportInterface $model */
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Import with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseImportInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the warehouse import: %1',
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
