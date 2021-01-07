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



namespace Mirasvit\Credit\Observer\Earning;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Mirasvit\Credit\Helper\Calculation;
use Mirasvit\Credit\Model\BalanceFactory;
use Mirasvit\Credit\Model\Config;
use Mirasvit\Credit\Model\EarningFactory;

abstract class AbstractObserver implements ObserverInterface
{
    /**
     * @var Calculation
     */
    protected $calculationHelper;

    /**
     * @var \Mirasvit\Credit\Model\BalanceFactory
     */
    protected $balanceFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var EarningFactory
     */
    protected $earningFactory;

    /**
     * AbstractObserver constructor.
     * @param Calculation $calculationHelper
     * @param BalanceFactory $balanceFactory
     * @param Config $config
     * @param EarningFactory $earningFactory
     */
    public function __construct(
        Calculation $calculationHelper,
        BalanceFactory $balanceFactory,
        Config $config,
        EarningFactory $earningFactory
    ) {
        $this->calculationHelper = $calculationHelper;
        $this->balanceFactory    = $balanceFactory;
        $this->config            = $config;
        $this->earningFactory    = $earningFactory;
    }

    /**
     * @param Order $order
     * @return \Mirasvit\Credit\Model\Balance
     */
    protected function getBalance($order)
    {
        return $this->balanceFactory->create()
            ->loadByCustomer($order->getCustomerId(), $order->getOrderCurrencyCode());
    }
}