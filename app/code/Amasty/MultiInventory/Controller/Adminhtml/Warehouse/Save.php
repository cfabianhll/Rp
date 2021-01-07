<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Warehouse;

use Amasty\MultiInventory\Api\WarehouseItemRepositoryInterface;
use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\Warehouse\CustomerGroupFactory;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Model\Warehouse\ShippingFactory;
use Amasty\MultiInventory\Model\Warehouse\StoreFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\DecoderInterface;

/**
 * Class Save
 */
class Save extends \Amasty\MultiInventory\Controller\Adminhtml\Warehouse
{
    /**
     * @var DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var CustomerGroupFactory
     */
    private $groupFactory;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var WarehouseFactory
     */
    private $factory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var WarehouseItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * Save constructor.
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param WarehouseRepositoryInterface $repository
     * @param CustomerGroupFactory $groupFactory
     * @param StoreFactory $storeFactory
     * @param ShippingFactory $shippingFactory
     * @param WarehouseFactory $factory
     * @param ItemFactory $itemFactory
     * @param WarehouseItemRepositoryInterface $itemRepository
     * @param DecoderInterface $jsonDecoder
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        WarehouseRepositoryInterface $repository,
        CustomerGroupFactory $groupFactory,
        StoreFactory $storeFactory,
        ShippingFactory $shippingFactory,
        WarehouseFactory $factory,
        ItemFactory $itemFactory,
        WarehouseItemRepositoryInterface $itemRepository,
        DecoderInterface $jsonDecoder
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->repository = $repository;
        $this->groupFactory = $groupFactory;
        $this->storeFactory = $storeFactory;
        $this->shippingFactory = $shippingFactory;
        $this->factory = $factory;
        $this->itemFactory = $itemFactory;
        $this->itemRepository = $itemRepository;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Index action
     *
     * @return Redirect
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (empty($data)) {
            return $resultRedirect->setPath('*/*/');
        }
        $id = (int)$this->getRequest()->getParam('warehouse_id');

        if (empty($data['warehouse_id'])) {
            unset($data['warehouse_id']);
        }

        if (!$id) {
            $model = $this->factory->create();

            if (isset($data['code'])) {
                $collection = $this->factory->create()->getCollection()->addFieldToFilter('code', $data['code']);

                if ($collection->getSize()) {
                    $this->messageManager->addErrorMessage(__('This warehouse code already exists.'));

                    return $resultRedirect->setPath('*/*/new');
                }
            }
        } else {
            $model = $this->repository->getById($id);

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This warehouse no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }
        }

        return $this->saveModel($model, $data, $id, $resultRedirect);
    }

    /**
     * @param Warehouse $model
     * @param array $data
     * @param int $id
     * @param Redirect $resultRedirect
     *
     * @return Redirect
     */
    private function saveModel($model, $data, $id, $resultRedirect)
    {
        list($data, $extData) = $this->unScopeData($data);
        $model->setData($data);

        try {
            if (!$id) {
                $this->repository->save($model);
            }
            $this->setExtData($model, $extData);
            $this->repository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the warehouse.'));
            $this->dataPersistor->clear('amasty_multi_inventory_warehouse');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['warehouse_id' => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the warehouse.'));
        }

        $this->dataPersistor->set('amasty_multi_inventory_warehouse', $data);

        return $resultRedirect->setPath(
            '*/*/edit',
            ['warehouse_id' => $this->getRequest()->getParam('warehouse_id')]
        );
    }

    /**
     * @param array $data
     * @return array
     */
    private function unScopeData($data)
    {
        $extData = [];
        $this->prepareData($data);

        if (isset($data['storeviews'])) {
            $extData['storeviews'] = $data['storeviews'];
            unset($data['storeviews']);
        }
        if (isset($data['customer_groups'])) {
            $extData['customer_groups'] = $data['customer_groups'];
            unset($data['customer_groups']);
        }
        if (isset($data['warehouse_products'])) {
            $extData['warehouse_products'] = $data['warehouse_products'];
            unset($data['warehouse_products']);
        }
        if (isset($data['shippings'])) {
            $extData['shippings'] = $data['shippings'];
            unset($data['shippings']);
        }

        return [$data, $extData];
    }

    /**
     * @param array $data
     */
    private function prepareData(&$data)
    {
        if (!$this->getRequest()->getParam('warehouse_id', 0)) {
            if (isset($data['order_email_notification']) && !$data['order_email_notification']) {
                $data['order_email_notification'] = $data['email'];
            }

            if (isset($data['low_stock_notification']) && !$data['low_stock_notification']) {
                $data['low_stock_notification'] = $data['email'];
            }
        }

        if (isset($data['state_id']) && $data['state_id']) {
            $data['state'] = $data['state_id'];
            unset($data['state_id']);
        }
    }

    /**
     * @param Warehouse $model
     * @param array $data
     *
     * @throws \Exception
     */
    private function setExtData($model, $data)
    {
        if (isset($data['storeviews'])) {
            $this->setExtStores($model, $data['storeviews']);
        }

        if (isset($data['customer_groups'])) {
            $this->setExtCustomerGroups($model, $data['customer_groups']);
        }

        if (isset($data['warehouse_products'])) {
            $this->setExtWarehouseProducts($model, $data['warehouse_products']);
        }

        if (isset($data['shippings'])) {
            $this->setExtShippings($model, $data['shippings']);
        }
    }

    /**
     * @param Warehouse $model
     * @param array $stores
     */
    private function setExtStores($model, $stores)
    {
        $ids = $model->getStoreIds();
        $newRows = array_diff($stores, $ids);
        $delRows = array_diff($ids, $stores);

        if (count($stores) > 1 && in_array(0, $stores)) {
            $delRows[] = 0;
        }
        if (count($delRows)) {
            foreach ($delRows as $id) {
                $model->deleteStore($id);
            }
        }

        if (count($newRows)) {
            foreach ($newRows as $id) {
                $object = $this->storeFactory->create();
                $object->setStoreId($id);
                $model->addStore($object);
            }
        }
    }

    /**
     * @param Warehouse $model
     * @param array $groups
     */
    private function setExtCustomerGroups($model, $groups)
    {
        $ids = $model->getGroupIds();
        $newRows = array_diff($groups, $ids);
        $delRows = array_diff($ids, $groups);

        if (count($delRows)) {
            foreach ($delRows as $id) {
                $model->deleteGroup($id);
            }
        }

        if (count($newRows)) {
            foreach ($newRows as $id) {
                $object = $this->groupFactory->create();
                $object->setGroupId($id);
                $model->addGroupCustomer($object);
            }
        }
    }

    /**
     * @param Warehouse $model
     * @param string $products
     *
     * @throws \Exception
     */
    private function setExtWarehouseProducts($model, $products)
    {
        $products = $this->jsonDecoder->decode($products);
        $ids = $model->getItemIds();
        $delRows = array_diff($ids, array_keys($products));

        if (count($delRows)) {
            foreach ($delRows as $id) {
                $model->deleteItems($id);
            }
        }

        foreach ($products as $id => $product) {
            $object = $this->itemFactory->create();
            $object->addData($product);
            $object->setProductId($id);
            $model->addItem($object);
        }
    }

    /**
     * @param Warehouse $model
     * @param array $shippingsData
     */
    private function setExtShippings($model, $shippingsData)
    {
        if (empty($shippingsData)) {
            return;
        }
        $shippings = [];

        foreach ($shippingsData as $code => $rate) {
            if (!empty($rate)) {
                $shippings[$code] = $rate;
            }
        }
        $codes = $model->getShippingsCodes();
        $newRows = array_keys($shippingsData);
        $delRows = $codes;

        if (count($delRows)) {
            foreach ($delRows as $code) {
                $model->deleteShipping($code);
            }
        }

        if (!count($newRows)) {
            return;
        }

        foreach ($newRows as $code) {
            if (isset($shippings[$code])) {
                $object = $this->shippingFactory->create();
                $object->setShippingMethod($code);
                $object->setRate($shippings[$code]);

                $model->addShippings($object);
            }
        }
    }
}
