<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Controller\Adminhtml\Warehouse;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Duplicate
 */
class Duplicate extends Warehouse
{
    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * Duplicate constructor.
     * @param Action\Context $context
     * @param WarehouseFactory $warehouseFactory
     * @param WarehouseRepositoryInterface $repository
     */
    public function __construct(
        Action\Context $context,
        WarehouseFactory $warehouseFactory,
        WarehouseRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->warehouseFactory = $warehouseFactory;
        $this->repository = $repository;
    }

    /**
     * Duplicate action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('warehouse_id');
        if ($id) {
            try {
                $model = $this->repository->getById($id);
                $duplicateModel = $this->warehouseFactory->create();
                $data = $model->getData();
                unset($data['warehouse_id']);
                $data['title'] = $this->changeField($data['title']);
                $data['code'] = $this->changeField($data['code']);
                $stores = $this->clearWarehouse($model->getStores(), $duplicateModel);
                $groups = $this->clearWarehouse($model->getCustomerGroups(), $duplicateModel);
                $items = $this->clearWarehouse($model->getItems(), $duplicateModel);
                $duplicateModel->setData($data);
                $duplicateModel->setStores($stores);
                $duplicateModel->setCustomerGroups($groups);
                $duplicateModel->setItems($items);
                $this->repository->save($duplicateModel);
                $duplicateModel->recalcInventory();
                $this->messageManager->addSuccessMessage(__('You duplicated the warehouse.'));
                return $resultRedirect->setPath('*/*/edit', ['warehouse_id' => $duplicateModel->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['warehouse_id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a warehouse to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $list
     * @param $model
     * @return mixed
     */
    private function clearWarehouse($list, $model)
    {
        foreach ($list as &$value) {
            $value->setId(null);
            $value->setWarehouse($model);
        }

        return $list;
    }

    /**
     * @param $name
     * @return string
     */
    private function changeField($name)
    {
        $array = explode("_", $name);
        if (count($array)> 1) {
            $value = end($array);
            $key = key($array);
            if ((int)$value > 0) {
                $array[$key] = ++$value;
            } else {
                $array[$key] = 1;
            }
            $name = implode("_", $array);
        } else {
            $name = join("_", [$name,1]);
        }

        return $name;
    }
}
