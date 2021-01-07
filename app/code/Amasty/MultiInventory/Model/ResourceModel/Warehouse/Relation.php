<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\ResourceModel\Warehouse;

use Amasty\MultiInventory\Api\WarehouseCustomerGroupRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseShippingRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseStoreRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationInterface;

/**
 * Class Relation
 */
class Relation implements RelationInterface
{
    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var WarehouseCustomerGroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var WarehouseStoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var WarehouseShippingRepositoryInterface
     */
    private $shippingRepository;

    /**
     * Relation constructor.
     *
     * @param WarehouseCustomerGroupRepositoryInterface $customerGroupRepository
     * @param WarehouseStoreRepositoryInterface $storeRepository
     * @param WarehouseItemRepositoryInterface $itemRepository
     * @param WarehouseShippingRepositoryInterface $shippingRepository
     */
    public function __construct(
        WarehouseCustomerGroupRepositoryInterface $customerGroupRepository,
        WarehouseStoreRepositoryInterface $storeRepository,
        WarehouseItemRepositoryInterface $itemRepository,
        WarehouseShippingRepositoryInterface $shippingRepository
    ) {
        $this->customerGroupRepository = $customerGroupRepository;
        $this->itemRepository = $itemRepository;
        $this->storeRepository = $storeRepository;
        $this->shippingRepository = $shippingRepository;
    }

    /**
     * Save relations for Warehouse
     *
     * @param AbstractModel $object
     *
     * @return void
     * @throws \Exception
     */
    public function processRelation(AbstractModel $object)
    {
        if ($object->getShippings() !== null) {
            $this->processShippings($object);
        }

        if ($object->getCustomerGroups() !== null) {
            $this->processCustomerGroups($object);
        }

        if ($object->getStores() !== null) {
            $this->processStores($object);
        }
        if ($object->getItems() !== null) {
            $this->processItems($object);
        }

        if (!$object->isGeneral()) {
            $object->recalcInventory();
        }
    }

    /**
     * @param AbstractModel $object
     *
     * @throws CouldNotSaveException
     */
    protected function processShippings($object)
    {
        foreach ($object->getShippings() as $item) {
            if (!$item->getWarehouseId()) {
                $item->setWarehouse($object);
            }
            $this->shippingRepository->save($item);
        }
    }

    /**
     * @param AbstractModel $object
     *
     * @throws CouldNotSaveException
     */
    protected function processCustomerGroups($object)
    {
        foreach ($object->getCustomerGroups() as $group) {
            if (!$group->getWarehouseId()) {
                $group->setWarehouse($object);
            }
            $this->customerGroupRepository->save($group);
        }
    }

    /**
     * @param AbstractModel $object
     *
     * @throws CouldNotSaveException
     */
    protected function processStores($object)
    {
        foreach ($object->getStores() as $store) {
            if (!$store->getWarehouseId()) {
                $store->setWarehouse($object);
            }
            $this->storeRepository->save($store);
        }
    }

    /**
     * @param AbstractModel $object
     *
     * @throws CouldNotSaveException
     */
    protected function processItems($object)
    {
        foreach ($object->getItems() as $item) {
            if (!$item->getWarehouseId()) {
                $item->setWarehouse($object);
            }
            $this->itemRepository->save($item);
        }
    }
}
