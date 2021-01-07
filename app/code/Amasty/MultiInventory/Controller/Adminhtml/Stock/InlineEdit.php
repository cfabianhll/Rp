<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Controller\Adminhtml\Stock;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface as WarehouseRepository;
use Amasty\MultiInventory\Controller\Adminhtml\Stock;
use Amasty\MultiInventory\Model\Indexer\Warehouse\Processor;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Model\Warehouse\ItemRepository;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use RuntimeException;

/**
 * Class InlineEdit
 */
class InlineEdit extends Stock
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var WarehouseRepository
     */
    private $warehouseRepository;

    /**
     * @var WarehouseFactory
     */
    private $warehouseFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var Processor
     */
    private $indexer;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * InlineEdit constructor.
     *
     * @param Context                                                  $context
     * @param WarehouseRepository                                      $warehouseRepository
     * @param WarehouseFactory                                         $warehouseFactory
     * @param ItemFactory                                              $itemFactory
     * @param JsonFactory                                              $jsonFactory
     * @param ItemRepository                                           $itemRepository
     * @param Processor $indexer
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        WarehouseRepository $warehouseRepository,
        WarehouseFactory $warehouseFactory,
        ItemFactory $itemFactory,
        JsonFactory $jsonFactory,
        ItemRepository $itemRepository,
        Processor $indexer,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->warehouseRepository = $warehouseRepository;
        $this->jsonFactory = $jsonFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->itemFactory = $itemFactory;
        $this->itemRepository = $itemRepository;
        $this->indexer = $indexer;
        $this->registry = $registry;
    }

    /**
     * @return ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }
        list($changedProducts, $error) = $this->updateItems($postItems, $messages);

        if (!$error && !empty($changedProducts)) {
            $this->indexer->reindexList($changedProducts); // recalculate total stock
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param array $postItems
     * @param array $messages
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function updateItems($postItems, &$messages)
    {
        $error = false;
        $changedProducts = [];

        foreach ($postItems as $item) {
            $productId = $item['entity_id'];
            $warehouses = $this->scopeWh($item);

            try {
                if (!empty($warehouses)) {
                    foreach ($warehouses as $warehouse) {
                        $this->change($productId, $warehouse);
                        $changedProducts[] = $productId;
                    }
                }
            } catch (LocalizedException $e) {
                $messages[] = $e->getMessage();
                $error = true;
            } catch (RuntimeException $e) {
                $messages[] = $e->getMessage();
                $error = true;
            } catch (Exception $e) {
                $messages[] = __('Something went wrong while saving the stock.');
                $error = true;
            }
        }

        return [$changedProducts, $error];
    }

    /**
     * @param $product
     * @param $warehouse
     *
     * @throws NoSuchEntityException
     */
    private function change($product, $warehouse)
    {
        $item = $this->itemRepository->getByProductWarehouse($product, $warehouse['id']);

        if (!$item->getId() && $warehouse['qty'] == 0 && $warehouse['room'] == '') {
            return; // Prevent creation of zero records
        }
        $model = $this->warehouseRepository->getById($warehouse['id']);

        if (!$model->isGeneral()) {
            $this->registry->register('am_dont_recalc_availability', true, true);
            $object = $this->itemFactory->create();
            $object->setProductId($product);
            $object->setQty($warehouse['qty']);
            $object->setRoomShelf($warehouse['room']);
            $object->setStockStatus($warehouse['stock_status']);
            $model->addItem($object);
        }
    }

    /**
     * @param $items
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function scopeWh($items)
    {
        $fields = ['qty', 'room', 'stock_status'];
        $stocksWh = [];

        foreach ($items as $key => $value) {
            if ($key != 'entity_id') {
                $id = $this->warehouseRepository->getByCode($key)->getId();
                $stocksWh[$key]['id'] = $id;

                foreach ($value as $field => $text) {
                    if (in_array($field, $fields)) {
                        $stocksWh[$key][$field] = $text;
                    }
                }
            }
        }

        return $stocksWh;
    }
}
