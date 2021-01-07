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

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Credit\Model\BalanceFactory;
use Mirasvit\Credit\Model\ResourceModel\Balance\CollectionFactory as BalanceCollectionFactory;
use Mirasvit\Credit\Model\Config;

class Data extends AbstractHelper
{
    /**
     * @var BalanceFactory
     */
    protected $balanceFactory;

    /**
     * @var BalanceCollectionFactory
     */
    protected $balanceCollectionFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param BalanceFactory $balanceFactory
     * @param BalanceCollectionFactory $balanceCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        BalanceFactory $balanceFactory,
        BalanceCollectionFactory $balanceCollectionFactory,
        StoreManagerInterface $storeManager,
        Config $config,
        Context $context
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->balanceFactory = $balanceFactory;
        $this->balanceCollectionFactory = $balanceCollectionFactory;
        $this->storeManager = $storeManager;
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * @param int    $customerId
     * @param string $currencyCode
     * @return \Mirasvit\Credit\Model\Balance
     */
    public function getBalance($customerId = null, $currencyCode = null)
    {
        if (!$customerId) {
            $customerId = $this->customerSession->getCustomerId();
        }
        if (!$currencyCode) {
            $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        }

        return $this->balanceFactory->create()
            ->loadByCustomer($customerId, $currencyCode);
    }

    /**
     * @param int $customerId
     * @return \Mirasvit\Credit\Model\ResourceModel\Balance\Collection
     */
    public function getCustomerBalances($customerId)
    {
        return $this->balanceCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * @return bool
     */
    public function isAutoRefundEnabled()
    {
        return $this->config->isAutoRefundEnabled();
    }

    /**
     * Check that shopping cart not contains store credit products
     *
     * @param \Magento\Quote\Model\Quote|null $quote Used in backend order creation form
     *
     * @return bool
     */
    public function isAllowedForQuote($quote = null)
    {
        if (!$quote) {
            $quote = $this->getQuote();
        }
        if (!$this->customerSession->getCustomerId() && !$quote->getCustomer()->getId()) {
            return false;
        }
        /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
        foreach ($quote->getItemsCollection() as $item) {
            if (($this->config->getRefillProduct() &&
                    $item->getProductId() == $this->config->getRefillProduct()->getId()
                ) ||
                $item->getProductType() == \Mirasvit\Credit\Model\Product\Type::TYPE_CREDITPOINTS
            ) {
                return false;
            }
        }

        return true;
    }
}
