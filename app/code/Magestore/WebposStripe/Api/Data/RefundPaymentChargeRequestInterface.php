<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Api\Data;

/**
 * Interface - RefundPaymentAmountRequestInterface
 */
interface RefundPaymentChargeRequestInterface
{
    const AMOUNT = 'amount';
    const PAYMENT_CHARGE_ID = 'payment_charge_id';

    /**
     * Get Amount
     *
     * @return float|string|null
     */
    public function getAmount();

    /**
     * Set Amount
     *
     * @param float|string|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeRequestInterface
     */
    public function setAmount($value);

    /**
     * Get Payment Charge Id
     *
     * @return string|float|null
     */
    public function getPaymentChargeId();

    /**
     * Set Payment Charge Id
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeRequestInterface
     */
    public function setPaymentChargeId($value);
}
