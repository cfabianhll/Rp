<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Quote\Observer\Webapi;

use Amasty\MultiInventory\Helper\System;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

/**
 * Class SubmitObserver
 */
class SubmitObserver
{
    /**
     * @var System
     */
    private $system;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * SubmitObserver constructor.
     *
     * @param System $system
     * @param Registry $registry
     */
    public function __construct(
        System $system,
        Registry $registry
    ) {
        $this->system = $system;
        $this->registry = $registry;
    }

    /**
     * avoid send email for Core. Email will be sent by Multiinventory.
     * Becouse order can be splitted
     *
     * @param \Magento\Quote\Observer\Webapi\SubmitObserver $submitObserver
     * @param Observer $observer
     */
    public function beforeExecute(
        \Magento\Quote\Observer\Webapi\SubmitObserver $submitObserver,
        Observer $observer
    ) {
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($this->system->isMultiEnabled()) {
            $this->registry->unregister('multiinventory_cant_send_new_email');
            if (!$order->getCanSendNewEmailFlag()) {
                $this->registry->register('multiinventory_cant_send_new_email', true);
            }

            // avoid send email for Core. Email will be sent by Multiinventory. Becouse order can be splitted
            $order->setCanSendNewEmailFlag(false);
        }
    }
}
