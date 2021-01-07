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



namespace Mirasvit\Credit\Model\Total\Quote;

use Mirasvit\Credit\Helper\Calculation;
use Mirasvit\Credit\Helper\Data;
use Mirasvit\Credit\Model\Config;
use Mirasvit\Credit\Service\Config\CalculationConfig;
use Mirasvit\Credit\Service\Quote\Item\PriceService;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Tax\Model\Config as TaxConfig;

class Credit extends AbstractTotal
{
    /**
     * @var array
     */
    private $processedShippingAddresses = [];
    /**
     * @var int
     */
    private $amountUsed = 0;
    /**
     * @var int
     */
    private $baseAmountUsed = 0;
    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var PriceService
     */
    private $priceService;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;
    /**
     * @var CalculationConfig
     */
    private $calculationConfig;
    /**
     * @var Calculation
     */
    private $calculationHelper;
    /**
     * @var PricingHelper
     */
    private $currencyHelper;
    /**
     * @var Data
     */
    private $creditHelper;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var TaxConfig
     */
    private $taxConfig;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Credit constructor.
     * @param PriceService $priceService
     * @param ProductMetadataInterface $productMetadata
     * @param Manager $manager
     * @param CalculationConfig $calculationConfig
     * @param Calculation $calculationHelper
     * @param PricingHelper $currencyHelper
     * @param Data $creditHelper
     * @param TaxConfig $taxConfig
     * @param Config $config
     * @param RequestInterface $request
     */
    public function __construct(
        PriceService $priceService,
        ProductMetadataInterface $productMetadata,
        Manager $manager,
        CalculationConfig $calculationConfig,
        Calculation $calculationHelper,
        PricingHelper $currencyHelper,
        Data $creditHelper,
        TaxConfig $taxConfig,
        Config $config,
        RequestInterface $request
    ) {
        $this->priceService      = $priceService;
        $this->productMetadata   = $productMetadata;
        $this->manager           = $manager;
        $this->calculationConfig = $calculationConfig;
        $this->calculationHelper = $calculationHelper;
        $this->currencyHelper    = $currencyHelper;
        $this->creditHelper      = $creditHelper;
        $this->taxConfig         = $taxConfig;
        $this->config            = $config;
        $this->request           = $request;
    }

    /**
     * @param Quote                       $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param AddressTotal                $total
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        AddressTotal $total
    ) {
        if ($quote->getUseCredit() == Config::USE_CREDIT_NO || !$this->creditHelper->isAllowedForQuote($quote)) {
            return $this;
        }


        if ($quote->getUseCredit() == Config::USE_CREDIT_UNDEFINED && !$this->config->isAutoApplyEnabled()) {
            return $this;
        }

        if (!$quote->getCustomer() || !$quote->getCustomer()->getId()) {
            return $this;
        }

        if ($this->manager->isEnabled('Mirasvit_Rewards') && $this->taxConfig->applyTaxAfterDiscount()) {
            $objectManager = ObjectManager::getInstance();
            $rewardsConfig = $objectManager->get('Mirasvit\Rewards\Model\Config');
            if (!$rewardsConfig->getDisableRewardsCalculation()) {
                return $this;
            }
        }

        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }

        $quote->setUseCredit(Config::USE_CREDIT_YES);

        $this->fixQuoteRegion($quote);

        if (version_compare($this->productMetadata->getVersion(), "2.2.2", ">=") &&
            version_compare($this->productMetadata->getVersion(), "2.3.0", "<")
        ) {
            // fix of magento bug https://github.com/magento/magento2/issues/12993
            // https://github.com/mirasvit/module-rewards/issues/183
            // PHP Fatal error:  Uncaught TypeError: Argument 1 passed to
            // Magento\Quote\Model\Cart\Totals::setExtensionAttributes() must be an instance of
            // Magento\Quote\Api\Data\TotalsExtensionInterface, instance of Magento\Quote\Api\Data\AddressExtension given,
            if ($quote->isVirtual()) {
                $addressTotalsData = $quote->getBillingAddress()->getData();
                if (isset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
                    unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
                    $quote->getBillingAddress()->setData($addressTotalsData)->save();
                }
            } else {
                $addressTotalsData = $quote->getShippingAddress()->getData();
                if (isset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
                    unset($addressTotalsData[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]);
                    $quote->getShippingAddress()->setData($addressTotalsData)->save();
                }
            }
        }
        $quote->setBaseCreditAmountUsed(0)
            ->setCreditAmountUsed(0)
            ->save();

        $this->resetMultishippingTotalsOnRecollection($quote, $address->getId());

        $balance = $this->creditHelper->getBalance($quote->getCustomerId(), $quote->getQuoteCurrencyCode());
        $balance->setTransactionCurrencyCode($quote->getQuoteCurrencyCode());
        $balanceAmount = $balance->getAmount();
        if ($quote->getQuoteCurrencyCode() != $balance->getCurrencyCode()) {
            $balanceAmount = $this->calculationHelper->convertToCurrency(
                $balance->getAmount(), $balance->getCurrencyCode(), $quote->getQuoteCurrencyCode(), $quote->getStore()
            );
            $balanceAmount = round($balanceAmount, 2, PHP_ROUND_HALF_DOWN);
        }
        $used = $baseUsed = $balanceAmount;
        if ($quote->getManualUsedCredit() > 0 && $used >= $quote->getManualUsedCredit()) {
            $used = $quote->getManualUsedCredit();
        }
        $baseUsed = $this->calculationHelper->convertToCurrency(
            $used, $quote->getQuoteCurrencyCode(), $balance->getCurrencyCode(), $quote->getStore()
        );

        $customerUsed = $used;
        $customerBaseUsed = $baseUsed;
        if ($quote->getIsMultiShipping()) {
            $used -= $this->amountUsed;
            $baseUsed -= $this->baseAmountUsed;
        } else {
            $this->amountUsed = $used;
            $this->baseAmountUsed = $baseUsed;
        }

        if ($total->getGrandTotal()) {
            $quoteTotal = $total->getGrandTotal();
            $quoteBaseTotal = $total->getBaseGrandTotal();
        } else {
            $quoteTotal = array_sum($total->getAllTotalAmounts());
            $quoteBaseTotal = array_sum($total->getAllBaseTotalAmounts());
        }
        if ($used > $quoteTotal && $quoteTotal >= 0) {
            $used     = $quoteTotal;
            $baseUsed = $quoteBaseTotal;
        }
        $maxUsed = $this->calculationHelper->calc(
            $quoteTotal, $total->getTotalAmount('tax'), $total->getTotalAmount('shipping')
        );
        if ($maxUsed < $used) {
            $used = $maxUsed;
        }
        $maxBaseUsed = $this->calculationHelper->calc(
            $quoteBaseTotal, $total->getBaseTotalAmount('tax'), $total->getBaseTotalAmount('shipping')
        );
        if ($maxBaseUsed < $baseUsed) {
            $baseUsed = $maxBaseUsed;
        }

        if ($quote->getIsMultiShipping()) {
            $this->amountUsed += $used;
            $this->baseAmountUsed += $baseUsed;

            if ($this->amountUsed > $customerUsed) {
                $this->amountUsed = $customerUsed;
                $this->baseAmountUsed = $customerBaseUsed;
            }

            $this->processedShippingAddresses[$address->getId()] = $used;
        } else {
            $this->amountUsed = $used;
            $this->baseAmountUsed = $baseUsed;
        }

        $quote->setBaseCreditAmountUsed($this->baseAmountUsed)
            ->setCreditAmountUsed($this->amountUsed);

        $address->setBaseCreditAmount($baseUsed)
            ->setCreditAmount($used)
            ->save();

        $itemsTotal = 0;
        $itemsBaseTotal = 0;

        /** @var \Magento\Quote\Model\Quote\Address\Item $item */
        foreach ($items as $item) {
            $itemsBaseTotal += $this->priceService->calcItemBasePrice($item);
            $itemsTotal     += $this->priceService->calcItemPrice($item);
        }
        $itemsBaseDiscount = $itemsDiscount = 0;
        foreach ($items as $item) {
            if ($itemsBaseTotal > 0) {
                if (!($itemBasePrice = $this->priceService->calcItemBasePrice($item))) {
                    continue;
                }
                $baseDiscount = $itemBasePrice / $itemsBaseTotal * $baseUsed;
                $itemPrice = $this->priceService->calcItemPrice($item);
                $discount     = $itemPrice / $itemsTotal * $used;
                if ($baseDiscount > $itemBasePrice) {
                    $baseDiscount = $itemBasePrice;
                }
                if ($discount > $itemPrice) {
                    $discount = $itemPrice;
                }

                $itemsBaseDiscount += $baseDiscount;
                $itemsDiscount += $discount;
                $item->setDiscountAmount($item->getDiscountAmount() + $discount);
                $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $baseDiscount);
                // credits should applied here, not in CreditDiscount
                if ($this->calculationConfig->getCreditTotalOrder() < 410) {
                    $item->setStoreCreditDiscount($discount);
                    $item->setStoreCreditBaseDiscount($baseDiscount);
                }
            }
        }
        if ($this->calculationConfig->IsShippingIncluded($quote->getStore()) && $baseUsed > $itemsBaseDiscount) {
            $baseShippingDiscount = $baseUsed - $itemsBaseDiscount;
            if ($address->getBaseShippingInclTax()) {
                $baseShippingAmount = $address->getBaseShippingInclTax();
            } else {
                $baseShippingAmount = $address->getBaseShippingInclTax() + $address->getBaseShippingTaxAmount() +
                    $address->getBaseShippingDiscountTaxCompensationAmnt();
            }
            if ($baseShippingAmount < $baseShippingDiscount) {
                $baseShippingDiscount = $baseShippingAmount;
            }
            $address->setBaseShippingDiscountAmount(
                $address->getBaseShippingDiscountAmount() + $baseShippingDiscount
            );
            $total->setBaseShippingDiscountAmount(
                $total->getBaseShippingDiscountAmount() + $baseShippingDiscount
            );
            $shippingDiscount = $used - $itemsDiscount;
            if ($address->getShippingInclTax()) {
                $shippingAmount = $address->getShippingInclTax();
            } else {
                $shippingAmount = $address->getShippingAmount() + $address->getShippingTaxAmount() +
                    $address->getShippingDiscountTaxCompensationAmnt();
            }
            if ($shippingAmount < $shippingDiscount) {
                $shippingDiscount = $shippingAmount;
            }
            $address->setShippingDiscountAmount($address->getShippingDiscountAmount() + $shippingDiscount);
            $total->setShippingDiscountAmount($total->getShippingDiscountAmount() + $shippingDiscount);
        }

        $total->setBaseTotalAmount($this->getCode(), -$baseUsed);
        $total->setTotalAmount($this->getCode(), -$used);

        if ($total->getBaseGrandTotal()) {
            $total->setBaseGrandTotal($quoteBaseTotal - $baseUsed);
        }
        if ($total->getGrandTotal()) {
            $total->setGrandTotal($quoteTotal - $used);
        }

        $total->setBaseCreditAmount($baseUsed);
        $total->setCreditAmount($used);

        $quote->setCreditCollected(true)
            ->save();

        return $this;
    }

    /**
     * @param Quote        $quote
     * @param AddressTotal $total
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, AddressTotal $total)
    {
        $creditTotal = null;
        if ($quote->getIsVirtual()) {
            $address = $quote->getBillingAddress();
        } else {
            $address = $quote->getShippingAddress();
        }

        if ($quote->getUseCredit() == Config::USE_CREDIT_YES && (float)$total->getCreditAmount()) {
            $creditTotal = [
                'code'  => $this->getCode(),
                'title' => __('Store Credit'),
                'value' => -$total->getCreditAmount(),
            ];

            $address->addTotal($creditTotal);
        }

        return $creditTotal;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int                        $addressId
     *
     * @return void
     */
    protected function resetMultishippingTotalsOnRecollection($quote, $addressId)
    {
        if (
            $quote->getIsMultiShipping()
            && !empty($this->processedShippingAddresses[$addressId])
            && $this->amountUsed
        ) {
            $this->amountUsed = 0;
            $this->processedShippingAddresses = [];
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return void
     */
    protected function fixQuoteRegion($quote)
    {
        /** @var \Magento\Customer\Model\Data\Region $region */
        /** @var \Magento\Framework\App\PageCache\Version|array $region */
        $region = $quote->getShippingAddress()->getRegion();
        if ($region instanceof \Magento\Customer\Model\Data\Region) {
            $quote->getShippingAddress()->setRegion($region->getRegion());
        } elseif (is_array($region)) { //M2.2.x
            $quote->getShippingAddress()->setRegion($region['region'] ?: '');
        }
        $region = $quote->getBillingAddress()->getRegion();
        if ($region instanceof \Magento\Customer\Model\Data\Region) {
            $quote->getBillingAddress()->setRegion($region->getRegion());
        } elseif (is_array($region)) { //M2.2.x
            $quote->getBillingAddress()->setRegion($region['region'] ?: '');
        }
    }
}
