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
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Service\Rma\RmaManagement;

use Magento\Framework\Exception\LocalizedException;
use Mirasvit\Rma\Model\Carrier\RmaFree;

class CreateReplacementOrder implements \Mirasvit\Rma\Api\Service\Rma\RmaManagement\CreateReplacementOrderInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Mirasvit\Rma\Api\Config\BackendConfigInterface
     */
    private $rmaBackendConfig;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
     */
    private $itemManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;

    /**
     * CreateReplacementOrder constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Config\BackendConfigInterface $rmaBackendConfig
     * @param \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Config\BackendConfigInterface $rmaBackendConfig,
        \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface $itemManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
    ) {
        $this->productRepository = $productRepository;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->rmaBackendConfig = $rmaBackendConfig;
        $this->itemManagement = $itemManagement;
        $this->rmaManagement = $rmaManagement;
        $this->rmaSearchManagement = $rmaSearchManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function create(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $paymentMethod = 'free';
        $originOrder = $this->rmaManagement->getOrder($rma);
        $orderStore = $this->storeManager->getStore($originOrder->getStoreId());

        $cartId = $this->cartManagement->createEmptyCart();
        /** @var \Magento\Quote\Model\Quote $cart */
        $cart = $this->cartRepository->get($cartId);
        $cart->setStore($orderStore);
        $cart->setCurrency($originOrder->getCurrency());
        if ($rma->getCustomerId()) {
            $customer = $this->customerRepository->getById($originOrder->getCustomerId());
            $cart->assignCustomer($customer);
        } else {
            $cart->setCheckoutMethod('guest');
            $cart->setCustomerEmail($originOrder->getCustomerEmail());
        }

        $hasExchangeItems = false;
        $items = $this->getItems($rma);
        foreach ($items as $item) {
            if ($this->itemManagement->isExchange($item)) {
                $product = $this->productRepository->get($item->getProductSku());
                $product->setPrice(0);
                $cart->addProduct($product, $item->getQtyRequested());
                $hasExchangeItems = true;
            }
        }
        if (!$hasExchangeItems) {
            throw new LocalizedException(
                __('At least one RMA item should have resolution "Exchange".')
            );
        }
        if (!$this->rmaBackendConfig->isRmaFreeShippingEnabled($orderStore->getWebsiteId())) {
            throw new LocalizedException(
                __('"RMA Free Shipping" method is required.')
            );
        }
        $cart->getBillingAddress()->addData($originOrder->getBillingAddress()->getData());
        if ($originOrder->getShippingAddress()) {
            $cart->getShippingAddress()->addData($originOrder->getShippingAddress()->getData());
            $cart->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod(RmaFree::SHIPPING_CODE . '_' . RmaFree::SHIPPING_CODE);
        }

        $cart->setPaymentMethod($paymentMethod);
        $cart->setInventoryProcessed(false);
        $cart->getPayment()->importData(['method' => $paymentMethod]);

        // Collect total and save
        $cart->collectTotals();

        // Submit the quote and create the order
        $cart->save();
        $cart = $this->cartRepository->get($cart->getId());

        $orderId = $this->cartManagement->placeOrder($cart->getId());
        $replacementOrderIds = $rma->getReplacementOrderIds();
        $replacementOrderIds[] = $orderId;
        $rma->setReplacementOrderIds($replacementOrderIds);
        $rma->save();

        return $orderId;
    }

    /**
     * @param \Mirasvit\Rma\Api\Data\RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    private function getItems(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        return $this->rmaSearchManagement->getRequestedItems($rma);
    }
}