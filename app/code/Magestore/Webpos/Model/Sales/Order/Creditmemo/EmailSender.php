<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Sales\Order\Creditmemo;

use Magestore\Webpos\Model\Checkout\Order;

/**
 * Class InvoiceService
 */
class EmailSender extends \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
{

    /**
     * Set the mail receptient
     *
     * @param string $email
     * @param string $name
     */
    public function setRecepient($email, $name)
    {
        $this->identityContainer->setCustomerEmail($email);
        $this->identityContainer->setCustomerName($name);
    }

    /**
     * Get the payment email HTML
     *
     * @param Order $order
     * @return string
     */
    public function getPaymentEmailHtml($order)
    {
        return $this->getPaymentHtml($order);
    }

    /**
     * Send the creditmemo to another email
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param string $email
     * @return bool
     */
    public function sendCreditmemoToAnotherEmail($creditmemo, $email)
    {
        $order = $creditmemo->getOrder();
        $order->setCustomerEmail($email);
        $creditmemo->setOrder($order);

        return parent::send($creditmemo, true);
    }
}
