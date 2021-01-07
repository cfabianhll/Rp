<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $blockCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();

        foreach ($items as $warehouse) {
            if ($stateId = (int)$warehouse->getState()) {
                $warehouse->setData('state', '');
            }
            $warehouse->setData('state_id', $stateId);

            $this->loadedData[$warehouse->getId()] = $warehouse->getData();
            $groups = [];

            foreach ($warehouse->getCustomerGroups() as $group) {
                $groups[] = $group->getGroupId();
            }
            $this->loadedData[$warehouse->getId()]['customer_groups'] = implode(",", $groups);
            $stores = [];

            foreach ($warehouse->getStores() as $store) {
                $stores[] = $store->getStoreId();
            }
            $this->loadedData[$warehouse->getId()]['storeviews'] = implode(",", $stores);
        }
        $data = $this->dataPersistor->get('amasty_multi_inventory_warehouse');

        if (!empty($data)) {
            $warehouse = $this->collection->getNewEmptyItem();
            $warehouse->setData($data);
            $this->loadedData[$warehouse->getId()] = $warehouse->getData();
            $this->dataPersistor->clear('amasty_multi_inventory_warehouse');
        }

        return $this->loadedData;
    }
}
