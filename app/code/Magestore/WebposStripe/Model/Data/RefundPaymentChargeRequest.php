<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Model\Data;

use Magestore\WebposStripe\Api\Data\RefundPaymentChargeRequestInterface;

/**
 * Webpos Stripe - Model - Data - Refund Payment Charge Request
 */
class RefundPaymentChargeRequest extends \Magento\Framework\DataObject implements RefundPaymentChargeRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount($value)
    {
        return $this->setData(self::AMOUNT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentChargeId()
    {
        return $this->getData(self::PAYMENT_CHARGE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentChargeId($value)
    {
        return $this->setData(self::PAYMENT_CHARGE_ID, $value);
    }
}
