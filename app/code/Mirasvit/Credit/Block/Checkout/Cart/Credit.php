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



namespace Mirasvit\Credit\Block\Checkout\Cart;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mirasvit\Credit\Helper\Data as CreditHelper;
use Mirasvit\Credit\Helper\Calculation;

class Credit extends Template
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var int
     */
    protected $isPaypal;

    /**
     * @var CreditHelper
     */
    protected $creditHelper;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param Calculation            $calculation
     * @param CreditHelper           $creditHelper
     * @param Context                $context
     * @param array                  $data
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Calculation $calculation,
        CreditHelper $creditHelper,
        Context $context,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->calculation   = $calculation;
        $this->creditHelper  = $creditHelper;
        $this->isPaypal      = isset($data['isPaypal']) ? $data['isPaypal'] : false;

        parent::__construct($context);
    }


    /**
     * @return \Mirasvit\Credit\Model\Balance
     */
    public function getBalance()
    {
        return $this->creditHelper->getBalance();
    }

    /**
     * @return float
     */
    public function getAmountToUse()
    {
        $quote = $this->creditHelper->getQuote();

        $toUse  = $quote->getGrandTotal();
        $totals = $quote->getTotals();

        $tax      = isset($totals['tax']) ? $totals['tax']->getValue() : 0;
        $shipping = isset($totals['shipping']) ? $totals['shipping']->getValue() : 0;

        $toUse = $this->calculation->calc($toUse, $tax, $shipping);

        if ($toUse > $this->getBalance()->getAmount()) {
            $toUse = $this->getBalance()->getAmount();
        }

        return $toUse;
    }

    /**
     * @return float
     */
    public function getUsedAmount()
    {
        return $this->creditHelper->getQuote()->getCreditAmountUsed();
    }

    /**
     * @return string
     */
    public function getBalanceAmount()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->creditHelper->getQuote();
        $balance = $this->creditHelper->getBalance($quote->getCustomerId(), $quote->getQuoteCurrencyCode());

        $amount = $balance->getAmount();
        if ($quote->getQuoteCurrencyCode() !== $balance->getCurrencyCode()) {
            $amount = $this->calculation->convertToCurrency(
                $amount,
                $balance->getCurrencyCode(),
                $quote->getQuoteCurrencyCode(),
                $quote->getStore()
            );
        }

        return $this->priceCurrency->format($amount, false);
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        if ($this->getBalance()->getAmount() > 0 && $this->creditHelper->isAllowedForQuote()) {
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function isPaypal()
    {
        return (int)$this->isPaypal;
    }
}
