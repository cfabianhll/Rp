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

use Mirasvit\Credit\Model\Config;
use Mirasvit\Credit\Service\Config\CalculationConfig;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;

class CreditDiscount extends AbstractTotal
{
    /**
     * @var CalculationConfig
     */
    private $calculationConfig;
    /**
     * @var Config
     */
    private $config;

    /**
     * CreditDiscount constructor.
     * @param CalculationConfig $calculationConfig
     * @param Config $config
     */
    public function __construct(
        CalculationConfig $calculationConfig,
        Config $config
    ) {
        $this->calculationConfig = $calculationConfig;
        $this->config = $config;
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
        if ($quote->getUseCredit() == Config::USE_CREDIT_NO) {
            return $this;
        }


        if ($quote->getUseCredit() == Config::USE_CREDIT_UNDEFINED && !$this->config->isAutoApplyEnabled()) {
            return $this;
        }

        if (!$quote->getCustomer() || !$quote->getCustomer()->getId()) {
            return $this;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        $items = $address->getAllItems();
        if (!count($items)) {
            return $this;
        }
        // credits should applied here, not in Model\Total\Quote\Credit
        if ($this->calculationConfig->getCreditTotalOrder() >= 410) {
            foreach ($items as $item) {
                if ($item->getStoreCreditBaseDiscount()) {
                    $item->setBaseDiscountAmount($item->getBaseDiscountAmount() + $item->getStoreCreditBaseDiscount());
                }
                if ($item->getStoreCreditDiscount()) {
                    $item->setDiscountAmount($item->getDiscountAmount() + $item->getStoreCreditDiscount());
                }
            }
        }
        return $this;
    }
}
