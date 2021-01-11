<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Api\Data;

/**
 * Interface - RefundPaymentChargeResponseInterface
 */
interface RefundPaymentChargeResponseInterface
{
    const ID = 'id';
    const OBJECT = 'object';
    const AMOUNT = 'amount';
    const BALANCE_TRANSACTION = 'balance_transaction';
    const CHARGE = 'charge';
    const CREATED = 'created';
    const CURRENCY = 'currency';
    const STATUS = 'status';
    const REASON = 'reason';

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
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setAmount($value);

    /**
     * Get Id
     *
     * @return string|float|null
     */
    public function getId();

    /**
     * Set Id
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setId($value);

    /**
     * Get Object
     *
     * @return string|float|null
     */
    public function getObject();

    /**
     * Set Object
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setObject($value);

    /**
     * Get Balance Transaction
     *
     * @return float|string|null
     */
    public function getBalanceTransaction();

    /**
     * Set Balance Transaction
     *
     * @param float|string|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setBalanceTransaction($value);

    /**
     * Get Charge
     *
     * @return string|float|null
     */
    public function getCharge();

    /**
     * Set Charge
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setCharge($value);

    /**
     * Get Created
     *
     * @return float|string|null
     */
    public function getCreated();

    /**
     * Set Created
     *
     * @param float|string|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setCreated($value);

    /**
     * Get Currency
     *
     * @return string|float|null
     */
    public function getCurrency();

    /**
     * Set Currency
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setCurrency($value);

    /**
     * Get Reason
     *
     * @return string|float|null
     */
    public function getReason();

    /**
     * Set Reason
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setReason($value);

    /**
     * Get Status
     *
     * @return string|float|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param string|float|null $value
     * @return \Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface
     */
    public function setStatus($value);
}
