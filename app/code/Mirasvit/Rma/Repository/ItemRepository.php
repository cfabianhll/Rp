<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Repository;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rma\Api\Data\ItemInterface;

/**
 * Select/insert/update of RMA items in DB
 */
class ItemRepository implements \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var array
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory
     */
    private $itemCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Item
     */
    private $itemResource;
    /**
     * @var \Mirasvit\Rma\Model\ItemFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * ItemRepository constructor.
     * @param \Mirasvit\Rma\Model\ItemFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Item $itemResource
     * @param \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     * @param \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ItemFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Item $itemResource,
        \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory         = $objectFactory;
        $this->itemResource          = $itemResource;
        $this->searchResultsFactory  = $searchResultsFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $this->itemResource->save($item);
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function get($itemId, $storeId = null)
    {
        $cacheKey = null !== $storeId ? $storeId : 'all';
        if (!isset($this->instances[$itemId][$cacheKey])) {
            /** @var \Mirasvit\Rma\Model\Item $item */
            $item = $this->objectFactory->create();
            if (null !== $storeId) {
                $item->setStoreId($storeId);
            }
            $item->load($itemId);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField('id', $itemId);
            }
            $this->instances[$itemId][$cacheKey] = $item;
        }
        return $this->instances[$itemId][$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderItemId($orderItemId)
    {
        if (!isset($this->instances['order_item'][$orderItemId])) {
            /** @var \Mirasvit\Rma\Model\Item $item */
            $item = $this->objectFactory->create();
            $this->itemResource->load($item, $orderItemId, ItemInterface::KEY_ORDER_ITEM_ID);
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField(ItemInterface::KEY_ORDER_ITEM_ID, $orderItemId);
            }
            $this->instances['order_item'][$orderItemId] = $item;
        }
        return $this->instances['order_item'][$orderItemId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        try {
            $itemId = $item->getId();
            $this->itemResource->delete($item);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete item with id %1',
                    $item->getId()
                ),
                $e
            );
        }
        unset($this->instances[$itemId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($itemId)
    {
        $item = $this->get($itemId);
        return  $this->delete($item);
    }

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\Item\Collection
     */
    public function getCollection()
    {
        return $this->itemCollectionFactory->create();
    }
}
