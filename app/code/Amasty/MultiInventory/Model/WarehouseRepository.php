<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model;

use Amasty\MultiInventory\Api\Data\WarehouseInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse as WarehouseResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class WarehouseRepository
 */
class WarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * @var WarehouseResource
     */
    protected $warehouseResource;

    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * WarehouseRepository constructor.
     *
     * @param Warehouse $warehouseResource
     * @param WarehouseFactory $warehouseFactory
     */
    public function __construct(
        WarehouseResource $warehouseResource,
        WarehouseFactory $warehouseFactory
    ) {
        $this->warehouseResource = $warehouseResource;
        $this->warehouseFactory = $warehouseFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseInterface $warehouse)
    {
        try {
            $this->warehouseResource->save($warehouse);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $warehouse;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($warehouseId)
    {
        /** @var WarehouseInterface $warehouse */
        $warehouse = $this->warehouseFactory->create();
        $this->warehouseResource->load($warehouse, $warehouseId);

        if (!$warehouse->getId()) {
            throw new NoSuchEntityException(__('Warehouse with id "%1" does not exist.', $warehouseId));
        }

        return $warehouse;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($warehouseCode)
    {
        $warehouse = $this->warehouseFactory->create();
        $this->warehouseResource->load($warehouse, $warehouseCode, WarehouseInterface::CODE);

        if (!$warehouse->getId()) {
            throw new NoSuchEntityException(__('Warehouse with code "%1" does not exist.', $warehouseCode));
        }

        return $warehouse;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseInterface $warehouse)
    {
        try {
            $this->warehouseResource->delete($warehouse);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Could not delete the warehouse: %1',
                    $exception->getMessage()
                )
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($warehouseId)
    {
        return $this->delete($this->getById($warehouseId));
    }
}
