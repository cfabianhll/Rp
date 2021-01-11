<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\WebposStripe\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Stripe observer - Creditmemo Webpos order payment save before
 */
class CreditmemoWebposOrderPaymentSaveBefore implements ObserverInterface
{
    /**
     * @var \Magestore\WebposStripe\Helper\Data
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Magestore\WebposStripe\Helper\Data $helper
     */
    public function __construct(
        \Magestore\WebposStripe\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magestore\Webpos\Model\Checkout\Order\Payment $paymentModel */
        $paymentModel = $observer->getData('webpos_payment');
        $isEnabled = $this->helper->isEnableStripe();
        if (!$isEnabled || $paymentModel->getMethod() !== 'stripe_integration') {
            return $this;
        }

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $this->helper->getObjectManager()->create(OrderRepositoryInterface::class);
        /** @var Order $order */
        $order = $orderRepository->get($paymentModel->getOrderId());
        $comment = "[Refund] Ref: {$paymentModel->getReferenceNumber()} by {$paymentModel->getTitle()}";
        $order->addCommentToStatusHistory($comment, false, true);
        $orderRepository->save($order);
        return $this;
    }
}
