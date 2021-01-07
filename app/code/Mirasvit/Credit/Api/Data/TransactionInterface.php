<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Api\Data;

interface TransactionInterface
{
    const ACTION_MANUAL    = 'manual';
    const ACTION_EARNING   = 'earning';
    const ACTION_REFUNDED  = 'refunded';
    const ACTION_USED      = 'used';
    const ACTION_REFILL    = 'refill';
    const ACTION_PURCHASED = 'purchased';

    const TABLE_NAME = 'mst_credit_transaction';

    const ID = 'transaction_id';

    const BALANCE_ID     = 'balance_id';
    const BALANCE_AMOUNT = 'balance_amount';
    const BALANCE_DELTA  = 'balance_delta';
    const CURRENCY_CODE  = 'currency_code';
    const ACTION         = 'action';
    const MESSAGE        = 'message';
    const IS_NOTIFIED    = 'is_notified';
    const CREATED_AT     = 'created_at';
    const UPDATED_AT     = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getBalanceId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setBalanceId($value);

    /**
     * @return float
     */
    public function getBalanceAmount();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setBalanceAmount($value);

    /**
     * @return float
     */
    public function getBalanceDelta();

    /**
     * @param float $value
     *
     * @return $this
     */
    public function setBalanceDelta($value);

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCurrencyCode($value);

    /**
     * @return string
     */
    public function getAction();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAction($value);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMessage($value);

    /**
     * @return bool
     */
    public function isNotified();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsNotified($value);

    /**
     * @return string
     */
    public function getCreatedAt();
}