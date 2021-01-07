<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Model\Warehouse;

use Amasty\MultiInventory\Api\Data\WarehouseQuoteItemInterface;
use Amasty\MultiInventory\Api\WarehouseQuoteItemRepositoryInterface;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item as QuoteItemResource;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse\Quote\ItemFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class QuoteItemRepository
 */
class QuoteItemRepository implements WarehouseQuoteItemRepositoryInterface
{
    /**
     * @var QuoteItemResource
     */
    private $resource;

    /**
     * @var ItemFactory
     */
    private $factory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $itemWarehouseStorage = [];

    /**
     * QuoteItemRepository constructor.
     *
     * @param QuoteItemResource $resource
     * @param CollectionFactory $collectionFactory
     * @param Quote\ItemFactory $factory
     */
    public function __construct(
        QuoteItemResource $resource,
        CollectionFactory $collectionFactory,
        ItemFactory $factory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(WarehouseQuoteItemInterface $item)
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
        /** @var WarehouseQuoteItemInterface $model */
        $model = $this->factory->create();
        $this->resource->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Warehouse Quote Item with id "%1" does not exist.', $id));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(WarehouseQuoteItemInterface $item)
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

    /**
     * {@inheritdoc}
     */
    public function getWarehouseIdByItem($quoteItem)
    {
        $quoteItemId = $quoteItem->getItemId();

        if (!isset($this->itemWarehouseStorage[$quoteItemId])) {
            $this->itemWarehouseStorage[$quoteItemId] = null;
            $warehouseQuote = $this->collectionFactory->create()
                ->addFieldToFilter(WarehouseQuoteItemInterface::QUOTE_ID, $quoteItem->getQuoteId())
                ->getData();

            if ($warehouseQuote) {
                foreach ($warehouseQuote as $warehouseData) {
                    $itemId = $warehouseData[WarehouseQuoteItemInterface::QUOTE_ITEM_ID];
                    $this->itemWarehouseStorage[$itemId]
                        = $warehouseData[WarehouseQuoteItemInterface::WAREHOUSE_ID];
                }

            }
        }

        return $this->itemWarehouseStorage[$quoteItemId];
    }
}
