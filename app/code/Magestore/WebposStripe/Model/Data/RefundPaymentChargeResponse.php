<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\WebposStripe\Model\Data;

use Magestore\WebposStripe\Api\Data\RefundPaymentChargeResponseInterface;

/**
 * Webpos Stripe - Model - Data - Refund Payment Charge Response
 */
class RefundPaymentChargeResponse extends \Magento\Framework\DataObject implements RefundPaymentChargeResponseInterface
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
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getObject()
    {
        return $this->getData(self::OBJECT);
    }

    /**
     * @inheritDoc
     */
    public function setObject($value)
    {
        return $this->setData(self::OBJECT, $value);
    }

    /**
     * @inheritDoc
     */
    public function getBalanceTransaction()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setBalanceTransaction($value)
    {
        return $this->setData(self::BALANCE_TRANSACTION, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCharge()
    {
        return $this->getData(self::CHARGE);
    }

    /**
     * @inheritDoc
     */
    public function setCharge($value)
    {
        return $this->setData(self::CHARGE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCreated()
    {
        return $this->getData(self::CREATED);
    }

    /**
     * @inheritDoc
     */
    public function setCreated($value)
    {
        return $this->setData(self::CREATED, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCurrency()
    {
        return $this->getData(self::CURRENCY);
    }

    /**
     * @inheritDoc
     */
    public function setCurrency($value)
    {
        return $this->setData(self::CURRENCY, $value);
    }

    /**
     * @inheritDoc
     */
    public function getReason()
    {
        return $this->getData(self::REASON);
    }

    /**
     * @inheritDoc
     */
    public function setReason($value)
    {
        return $this->setData(self::REASON, $value);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }
}
