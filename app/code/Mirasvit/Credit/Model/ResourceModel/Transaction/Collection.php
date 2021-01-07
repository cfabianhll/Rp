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



namespace Mirasvit\Credit\Model\ResourceModel\Transaction;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mirasvit\Credit\Api\Data\TransactionInterface;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            \Mirasvit\Credit\Model\Transaction::class,
            \Mirasvit\Credit\Model\ResourceModel\Transaction::class
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->joinBalance()
            ->joinCustomer();

        return $this;
    }

    /**
     * @return $this
     */
    protected function joinBalance()
    {
        $this->getSelect()->joinLeft(
            ['balance' => $this->getTable('mst_credit_balance')],
            'main_table.balance_id = balance.balance_id',
            ['balance_currency_code' => 'currency_code']
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function joinCustomer()
    {
        $nameExpr = new \Zend_Db_Expr('CONCAT(customer.firstname, " ", customer.lastname)');

        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'balance.customer_id = customer.entity_id',
            ['email' => 'email', 'customer_id' => 'entity_id', 'name' => $nameExpr, 'customer.website_id']
        );

        return $this;
    }

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function addFilterByCustomer($customerId)
    {
        $this->getSelect()
            ->where('balance.customer_id = ?', intval($customerId));

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalUsedAmount()
    {
        $this->_renderFilters();

        $select = clone $this->getSelect();
        $select->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET)
            ->reset(Select::COLUMNS)
            ->columns('SUM(balance_delta)')
            ->where('action=?', TransactionInterface::ACTION_USED);

        return abs($this->getConnection()->fetchOne($select));
    }
}
