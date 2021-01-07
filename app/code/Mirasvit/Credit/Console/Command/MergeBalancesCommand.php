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



namespace Mirasvit\Credit\Console\Command;

use Magento\Customer\Model\CustomerFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mirasvit\Credit\Helper\Calculation;
use Mirasvit\Credit\Model\BalanceFactory;
use Mirasvit\Credit\Model\ResourceModel\Balance\CollectionFactory as BalanceCollectionFactory;

class MergeBalancesCommand extends Command
{
    /**
     * @var BalanceFactory
     */
    private $balanceFactory;
    /**
     * @var BalanceCollectionFactory
     */
    private $balanceCollectionFactory;
    /**
     * @var Calculation
     */
    private $calculationHelper;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * MergeBalancesCommand constructor.
     * @param BalanceCollectionFactory $balanceCollectionFactory
     * @param BalanceFactory $balanceFactory
     * @param Calculation $calculationHelper
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        BalanceCollectionFactory $balanceCollectionFactory,
        BalanceFactory $balanceFactory,
        Calculation $calculationHelper,
        CustomerFactory $customerFactory
    ) {
        parent::__construct();

        $this->balanceCollectionFactory = $balanceCollectionFactory;
        $this->balanceFactory           = $balanceFactory;
        $this->calculationHelper        = $calculationHelper;
        $this->customerFactory          = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:credit:merge-balances')
            ->setDescription('Merge Customer multi currency balances in base currency balance');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->balanceCollectionFactory->create();
        $collection->getSelect()
            ->order(new \Zend_Db_Expr('customer_id ASC'));

        $customerBalance   = 0;
        $currentCustomerId = 0;
        $baseBalance       = null;
        $baseCurrency      = null;
        $customer          = null;

        /** @var \Mirasvit\Credit\Model\Balance $balance */
        foreach ($collection as $balance) {
            if ($currentCustomerId != $balance->getCustomerId()) {
                if ($customer) {
                    $baseBalance->setAmount($customerBalance)
                        ->save();
                }
                $currentCustomerId = $balance->getCustomerId();
                $customer          = $this->customerFactory->create()->load($currentCustomerId);
                $customerBalance   = 0;
                $baseCurrency      = $customer->getStore()->getDefaultCurrency()->getCurrencyCode();
                $baseBalance       = $this->getBaseBalance($customer, $baseCurrency);
            }
            $amount = $balance->getAmount();
            if ($customer && $baseCurrency != $balance->getCurrencyCode()) {
                $amount = $this->calculationHelper->convertToCurrency(
                    $amount, $balance->getCurrencyCode(), $baseCurrency, $customer->getStore()
                );
                if (!$baseBalance) {

                }
                $transactions = $balance->getTransactionCollection();
                /** @var \Mirasvit\Credit\Model\Transaction $transaction */
                foreach ($transactions as $transaction) {
                    $transaction->setBalanceId($baseBalance->getId())
                        ->setBalanceAmount(0)
                        ->save();
                }
                $balance->delete();
            }
            $customerBalance += $amount;
        }

        if (!$baseBalance && $customer) {
            $this->balanceFactory->create()
                ->setAmount($customerBalance)
                ->setCustomerId($customer->getId())
                ->setCurrencyCode($baseCurrency)
                ->save();
        } elseif ($baseBalance && $customer) {
            $baseBalance->setAmount($customerBalance)
                ->save();
        }
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param string                           $baseCurrency
     *
     * @return \Mirasvit\Credit\Model\Balance
     */
    protected function getBaseBalance($customer, $baseCurrency)
    {
        $baseBalanceCollection = $this->balanceCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('currency_code', $baseCurrency);
        if ($baseBalanceCollection->count()) {
            $baseBalance = $baseBalanceCollection->getFirstItem();
        } else {
            $baseBalance = $this->balanceFactory->create()
                ->setAmount(0)
                ->setCustomerId($customer->getId())
                ->setCurrencyCode($baseCurrency)
                ->save();
        }

        return $baseBalance;
    }
}