<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposShipping\Model\Order\Shipment;

use Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipInterface;

/**
 * Class ItemToShip
 *
 * Used to get set item to ship attribute
 */
class ItemToShip extends \Magento\Framework\Model\AbstractModel implements ItemToShipInterface
{
    /**
     * Get order item id
     *
     * @return int|null
     */
    public function getOrderItemId()
    {
        return $this->getData(self::ORDER_ITEM_ID);
    }

    /**
     * Set order item id
     *
     * @param int|null $orderItemId
     * @return ItemToShipInterface
     */
    public function setOrderItemId($orderItemId)
    {
        return $this->setData(self::ORDER_ITEM_ID, $orderItemId);
    }

    /**
     * Get qty to ship
     *
     * @return float|null
     */
    public function getQtyToShip()
    {
        return $this->getData(self::QTY_TO_SHIP);
    }

    /**
     * Set qty to ship
     *
     * @param float|null $qtyToShip
     * @return ItemToShipInterface
     */
    public function setQtyToShip($qtyToShip)
    {
        return $this->setData(self::QTY_TO_SHIP, $qtyToShip);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magestore\WebposShipping\Api\Data\Order\Shipment\ItemToShipExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
