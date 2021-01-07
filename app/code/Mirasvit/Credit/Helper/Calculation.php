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


namespace Mirasvit\Credit\Helper;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyHelper;
use Mirasvit\Credit\Api\Config\CalculationConfigInterface;
use Mirasvit\Credit\Model\Config;
use Mirasvit\Credit\Model\Balance;

class Calculation extends AbstractHelper
{
    /**
     * @var CurrencyHelper
     */
    private $currencyHelper;
    /**
     * @var CurrencyFactory
     */
    private $dirCurrencyFactory;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var CalculationConfigInterface
     */
    private $calculationConfig;

    /**
     * Calculation constructor.
     * @param CalculationConfigInterface $calculationConfig
     * @param Config $config
     * @param CurrencyFactory $dirCurrencyFactory
     * @param CurrencyHelper $currencyHelper
     * @param Context $context
     */
    public function __construct(
        CalculationConfigInterface $calculationConfig,
        Config $config,
        CurrencyFactory $dirCurrencyFactory,
        CurrencyHelper $currencyHelper,
        Context $context
    ) {
        parent::__construct($context);

        $this->calculationConfig = $calculationConfig;
        $this->config = $config;
        $this->currencyHelper  = $currencyHelper;
        $this->dirCurrencyFactory = $dirCurrencyFactory;
    }

    /**
     * @param float $credits
     * @param float $tax
     * @param float $shipping
     * @return float
     */
    public function calc($credits, $tax = 0.00, $shipping = 0.00)
    {
        if (!$this->calculationConfig->IsShippingIncluded()) {
            $credits -= $shipping;
        }
        if (!$this->calculationConfig->isTaxIncluded()) {
            $credits -= $tax;
        }

        return $credits;
    }

    /**
     * @param float $amount
     * @param Currency|string|null $fromCurrency
     * @param Currency|string|null $toCurrency
     * @param null|string|bool|int|\Magento\Framework\App\ScopeInterface $store
     *
     * @return float
     */
    public function convertToCurrency($amount, $fromCurrency, $toCurrency, $store)
    {
        if (!$fromCurrency instanceof Currency) {
            /** @var Currency $fromCurrency */
            $fromCurrency = $this->currencyHelper->getCurrency($store, $fromCurrency);
        }
        if (!$toCurrency instanceof Currency) {
            /** @var Currency $toCurrency */
            $toCurrency = $this->currencyHelper->getCurrency($store, $toCurrency);
        }
        try {
            $converted = $fromCurrency->convert($amount, $toCurrency);
        } catch (\Exception $e) {
            $converted = $this->calcCurrencyRate($amount, $fromCurrency, $toCurrency);
        }

        return $converted;
    }

    /**
     * @param float $amount
     * @param \Magento\Framework\Model\AbstractModel|Currency $fromCurrency
     * @param \Magento\Framework\Model\AbstractModel|Currency $toCurrency
     * @return float
     */
    public function calcCurrencyRate($amount, $fromCurrency, $toCurrency)
    {
        if (
            $fromCurrency->getCurrencyCode() == $toCurrency->getCurrencyCode() ||
            $this->config->getShareBalance() == Balance::SHARE_BALANCE_CURRENCY
        ) {
            return $amount;
        }

        $currencyModel = $this->dirCurrencyFactory->create();
        $rates = $currencyModel->getCurrencyRates(
            $fromCurrency->getCurrencyCode(), $toCurrency->getCurrencyCode()
        );
        if (!count($rates) || !isset($rates[$toCurrency->getCurrencyCode()])) {
            $rates = $currencyModel->getCurrencyRates(
                $toCurrency->getCurrencyCode(), $fromCurrency->getCurrencyCode()
            );
            $currencyRate = 1 / $rates[$fromCurrency->getCurrencyCode()];
            $rates[$toCurrency->getCurrencyCode()] = $currencyRate;
        }
        $rate = $rates[$toCurrency->getCurrencyCode()];

        return $this->currencyHelper->round($amount * $rate);
    }
}
