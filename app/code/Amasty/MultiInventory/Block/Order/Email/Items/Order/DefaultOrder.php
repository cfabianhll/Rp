<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Order\Email\Items\Order;

use Amasty\MultiInventory\Api\WarehouseRepositoryInterface;
use Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\GiftMessage\Helper\Message;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class DefaultOrder
 */
class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @var \Amasty\MultiInventory\Model\Warehouse\ItemFactory
     */
    private $factory;

    /**
     * @var WarehouseFactory
     */
    private $whFactory;

    /**
     * @var WarehouseRepositoryInterface
     */
    private $repository;

    /**
     * @var ItemFactory
     */
    private $orderFactory;

    /**
     * @var Message
     */
    private $messageHelper;

    /**
     * DefaultOrder constructor.
     * @param Template\Context $context
     * @param \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory
     * @param WarehouseFactory $whFactory
     * @param ItemFactory $orderFactory
     * @param WarehouseRepositoryInterface $repository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\MultiInventory\Model\Warehouse\ItemFactory $factory,
        WarehouseFactory $whFactory,
        ItemFactory $orderFactory,
        WarehouseRepositoryInterface $repository,
        Message $messageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->factory = $factory;
        $this->whFactory = $whFactory;
        $this->repository = $repository;
        $this->orderFactory = $orderFactory;
        $this->messageHelper = $messageHelper;
    }

    /**
     * @param OrderItem $item
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getItemWarehouse(OrderItem $item)
    {
        $collection = $this->getCollectionWh($item);
        if ($collection->getSize()) {
            $text = '';

            foreach ($collection as $orderItem) {
                // @codingStandardsIgnoreLine
                $text = $text . $orderItem->getWarehouse()->getTitle() . '<br/>';
            }
            return $text;
        }

        return $this->repository->getById($this->whFactory->create()->getDefaultId())->getTitle();
    }

    /**
     * @param OrderItem $item
     * @return string
     */
    public function getItemRoomShelf(OrderItem $item)
    {
        $collection = $this->getCollectionWh($item);
        $text = '';
        $products = [];
        if ($collection->getSize()) {
            foreach ($collection as $orderItem) {
                if ($item->getHasChildren()) {
                    foreach ($item->getChildrenItems() as $child) {
                        if ($orderItem->getOrderItemId() == $child->getId()) {
                            $products[] = $child->getProductId();
                        }
                    }
                } else {
                    $products[] = $item->getProductId();
                }
                $warehouse = $orderItem->getWarehouseId();
                $records = $this->factory->create()->getCollection()
                    ->addFieldToFilter('product_id', ['in' => $products])
                    ->addFieldToFilter('warehouse_id', $warehouse);
                foreach ($records as $record) {
                    // @codingStandardsIgnoreLine
                    $text .= $record->getRoomShelf() . '<br/>';
                }
            }
        }

        return $text;
    }

    /**
     * @param OrderItem $item
     *
     * @return $this
     */
    private function getCollectionWh(OrderItem $item)
    {
        $orderId = $this->getOrder()->getId();
        $itemId = $item->getId();
        $childs = [];
        if ($item->getHasChildren()) {
            foreach ($item->getChildrenItems() as $child) {
                $childs[] = $child->getId();
            }
        }
        if (!count($childs)) {
            $childs[] = $itemId;
        }
        return $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('order_item_id', ['in' => $childs])
            ->addFieldToFilter('order_id', $orderId);
    }

    /**
     * @param int|null $id
     *
     * @return \Magento\GiftMessage\Model\Message
     */
    public function getGiftMessage($id = null)
    {
        return $this->messageHelper->getGiftMessage($id);
    }
}
