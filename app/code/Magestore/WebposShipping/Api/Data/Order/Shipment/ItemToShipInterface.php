<?php

namespace Magestore\WebposShipping\Api\Data\Order\Shipment;

interface ItemToShipInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ORDER_ITEM_ID = 'order_item_id';
    const QTY_TO_SHIP = 'qty_to_ship';

    /**
     * Get order item id
     *
     * @return int|null
     */
    public function getOrderItemId();

    /**
     * Set order item id
     *
     * @param int|null $orderItemId
     * @return ItemToShipInterface
     */
    public function setOrderItemId($orderItemId);

    /**
     * Get qty to ship
     *
     * @return float|null
     */
    public function getQtyToShip();

    /**
     * Set qty to ship
     *
     * @param float|null $qtyToShip
     * @return ItemToShipInterface
     */
    public function setQtyToShip($qtyToShip);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipExtensionInterface $extensionAttributes
    );
}
