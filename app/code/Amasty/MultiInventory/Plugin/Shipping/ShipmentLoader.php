<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Shipping;

use Amasty\MultiInventory\Helper\System;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Shipment;

/**
 * Class ShipmentLoader
 */
class ShipmentLoader
{
    /**
     * @var System
     */
    private $system;

    public function __construct(System $system)
    {
        $this->system = $system;
    }

    /**
     * @param \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $subject
     * @param bool|Shipment                    $result
     *
     * @return bool|Shipment
     */
    public function afterLoad(
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $subject,
        $result
    ) {
        if (!$this->system->isMultiEnabled()) {
            return $result;
        }

        if ($result instanceof DataObject) {
            $shipmentData = $subject->getShipment();

            if (isset($shipmentData['warehouse'])) {
                $result->setData('warehouse', $shipmentData['warehouse']);
            }
        }

        return $result;
    }
}
