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

use Magento\Checkout\Model\ConfigProviderInterface;
use Mirasvit\Credit\Helper\Calculation;
use Mirasvit\Credit\Helper\Data as CreditHelper;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CreditHelper
     */
    protected $creditHelper;
    /**
     * @var Calculation
     */
    private $calculationHelper;

    /**
     * @param Calculation  $calculationHelper
     * @param CreditHelper $creditHelper
     */
    public function __construct(
        Calculation $calculationHelper,
        CreditHelper $creditHelper
    ) {
        $this->calculationHelper = $calculationHelper;
        $this->creditHelper = $creditHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->creditHelper->getQuote();
        $balance = $this->creditHelper->getBalance($quote->getCustomerId(), $quote->getQuoteCurrencyCode());

        $amount = $balance->getAmount();
        if ($quote->getQuoteCurrencyCode() !== $balance->getCurrencyCode()) {
            $amount = $this->calculationHelper->convertToCurrency(
                $amount,
                $balance->getCurrencyCode(),
                $quote->getQuoteCurrencyCode(),
                $quote->getStore()
            );
        }

        return [
            'creditConfig' => [
                'isAllowed'  => $this->creditHelper->isAllowedForQuote(),
                'amount'     => (float)$amount,
                'amountUsed' => $quote->getUseCredit() == Config::USE_CREDIT_YES,
            ],
        ];
    }
}
