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


namespace Mirasvit\Credit\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface as PricingHelper;
use Mirasvit\Credit\Helper\Data;
use Mirasvit\Credit\Helper\Calculation;

class Credit implements SectionSourceInterface
{
    /**
     * @var Calculation
     */
    private $calculation;

    /**
     * @var Data
     */
    protected $creditHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @param Calculation   $calculation
     * @param Data          $creditHelper
     * @param Session       $customerSession
     * @param PricingHelper $pricingHelper
     */
    public function __construct(
        Calculation $calculation,
        Data $creditHelper,
        Session $customerSession,
        PricingHelper $pricingHelper
    ) {
        $this->calculation = $calculation;
        $this->creditHelper = $creditHelper;
        $this->customerSession = $customerSession;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
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

        $amount = $this->pricingHelper->format($amount, false);

        return [
            'amount' => $amount,
        ];
    }

    /**
     * @return \Mirasvit\Credit\Model\Balance
     */
    public function getBalance()
    {
        return $this->creditHelper->getBalance();
    }
}
