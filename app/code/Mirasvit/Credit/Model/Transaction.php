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



namespace Mirasvit\Credit\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Mirasvit\Credit\Api\Data\TransactionInterface;
use Mirasvit\Credit\Helper\Message as MessageHelper;

class Transaction extends AbstractModel implements TransactionInterface
{

    /**
     * @var Balance
     */
    protected $balance;

    /**
     * @var BalanceFactory
     */
    protected $balanceFactory;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * Transaction constructor.
     * @param BalanceFactory $balanceFactory
     * @param MessageHelper $messageHelper
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        BalanceFactory $balanceFactory,
        MessageHelper $messageHelper,
        Context $context,
        Registry $registry
    ) {
        $this->balanceFactory = $balanceFactory;
        $this->messageHelper  = $messageHelper;

        parent::__construct($context, $registry);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Transaction::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalanceId()
    {
        return $this->getData(self::BALANCE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalanceId($value)
    {
        return $this->setData(self::BALANCE_ID, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalanceAmount()
    {
        return $this->getData(self::BALANCE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalanceAmount($value)
    {
        return $this->setData(self::BALANCE_AMOUNT, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalanceDelta()
    {
        return $this->getData(self::BALANCE_DELTA);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalanceDelta($value)
    {
        return $this->setData(self::BALANCE_DELTA, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($value)
    {
        return $this->setData(self::CURRENCY_CODE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setAction($value)
    {
        return $this->setData(self::ACTION, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($value)
    {
        return $this->setData(self::MESSAGE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function isNotified()
    {
        return $this->getData(self::IS_NOTIFIED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsNotified($value)
    {
        return $this->setData(self::IS_NOTIFIED, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @return Balance|false
     */
    public function getBalance()
    {
        if (!$this->getBalanceId()) {
            return false;
        }

        if ($this->balance === null) {
            $this->balance = $this->balanceFactory->create()->load($this->getBalanceId());
        }

        return $this->balance;
    }

    /**
     * @return string
     */
    public function getBackendMessage()
    {
        return $this->messageHelper->getBackendTransactionMessage($this->getMessage());
    }

    /**
     * @return string
     */
    public function getFrontendMessage()
    {
        return $this->messageHelper->getFrontendTransactionMessage($this->getMessage());
    }

    /**
     * @return string
     */
    public function getEmailMessage()
    {
        return $this->messageHelper->getEmailTransactionMessage($this->getMessage());
    }
}
