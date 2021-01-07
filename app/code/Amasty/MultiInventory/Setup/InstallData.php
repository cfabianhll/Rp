<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Setup;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\Warehouse\CustomerGroupFactory;
use Amasty\MultiInventory\Model\Warehouse\StoreFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\CatalogInventory\Model\Stock;
use Magento\Customer\Model\Group;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * Page factory
     *
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var CustomerGroupFactory
     */
    private $warehouseCustomerGroupFactory;

    /**
     * @var StoreFactory
     */
    private $warehouseStoreFactory;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * InstallData constructor.
     * @param WarehouseFactory $warehouseFactory
     * @param CustomerGroupFactory $warehouseCustomerGroupFactory
     * @param StoreFactory $warehouseStoreFactory
     * @param Group $group
     * @param WarehouseRepositoryInterface $repository
     */
    public function __construct(
        WarehouseFactory $warehouseFactory,
        CustomerGroupFactory $warehouseCustomerGroupFactory,
        StoreFactory $warehouseStoreFactory,
        Group $group,
        WarehouseRepositoryInterface $repository
    ) {
        $this->warehouseFactory = $warehouseFactory;
        $this->warehouseCustomerGroupFactory = $warehouseCustomerGroupFactory;
        $this->warehouseStoreFactory = $warehouseStoreFactory;
        $this->group = $group;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addTotalStock();
        $this->warehouseFactory->create()->getResource()->setDatafromInventory(
            $this->warehouseFactory->create()->getDefaultId(),
            $setup
        );
        $setup->endSetup();
    }

    /**
     * add Default Stock
     */
    public function addTotalStock()
    {
        $defaultData = [
            'title' => 'Total Stock',
            'code' => 'default',
            'description' => 'This warehouse is created during the module installation. ' .
                'This is default warehouse that represents the Total Stock. ' .
                'Every product created in Magento is assigned to this warehouse with total quantity. ' .
                'You are not allowed to delete it. You can use it as Total Stock or just ignore.',
            'manage' => 1,
            'is_general' => 1,
            'stock_id' => Stock::DEFAULT_STOCK_ID
        ];

        $warehouse = $this->warehouseFactory->create();
        $warehouse->setData($defaultData);
        $collection = $this->group->getCollection();
        foreach ($collection as $group) {
            $newGroup = $this->warehouseCustomerGroupFactory->create();
            $newGroup->setGroupId($group->getId());
            $warehouse->addGroupCustomer($newGroup);
        }
        $newStore = $this->warehouseStoreFactory->create();
        $newStore->setStoreId(0);
        $warehouse->addStore($newStore);
        $this->repository->save($warehouse);
    }
}
