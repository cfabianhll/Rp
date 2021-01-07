<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Sales\Model\AdminOrder;

use Amasty\MultiInventory\Helper\System;

/**
 * Class Create
 */
class Create
{
    /**
     * @var System
     */
    private $system;

    /**
     * SubmitObserver constructor.
     * @param System $system
     */
    public function __construct(
        System $system
    ) {
        $this->system = $system;
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $order
     */
    public function beforeCreateOrder(
        \Magento\Sales\Model\AdminOrder\Create $order
    ) {
        if ($this->system->isMultiEnabled() && $this->system->getDefinationWarehouse()) {
            $order->setSendConfirmation(false);
        }
    }
}
