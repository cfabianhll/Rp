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


namespace Mirasvit\Credit\Observer;

use Mirasvit\Credit\Model\Product\Type;
use Magento\Sales\Model\Order;
use Mirasvit\Credit\Api\Data\ProductOptionCreditInterface;

class OrderSaveCommitAfterObserver extends \Mirasvit\Credit\Observer\AbstractObserver
{
    /**
     * @var \Mirasvit\Credit\Helper\Calculation
     */
    private $culculationHelper;
    /**
     * @var \Mirasvit\Credit\Helper\CreditOption
     */
    private $optionHelper;
    /**
     * @var \Mirasvit\Credit\Model\ProductOptionCreditFactory
     */
    private $productOptionCreditFactory;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * OrderSaveCommitAfterObserver constructor.
     * @param \Mirasvit\Credit\Helper\CreditOption $optionHelper
     * @param \Mirasvit\Credit\Helper\Calculation $culculationHelper
     * @param \Mirasvit\Credit\Helper\Data $creditHelper
     * @param \Mirasvit\Credit\Model\ProductOptionCreditFactory $productOptionCreditFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Mirasvit\Credit\Helper\CreditOption $optionHelper,
        \Mirasvit\Credit\Helper\Calculation $culculationHelper,
        \Mirasvit\Credit\Helper\Data $creditHelper,
        \Mirasvit\Credit\Model\ProductOptionCreditFactory $productOptionCreditFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Model\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($creditHelper, $context, $storeManager);

        $this->optionHelper               = $optionHelper;
        $this->culculationHelper          = $culculationHelper;
        $this->productRepository          = $productRepository;
        $this->productOptionCreditFactory = $productOptionCreditFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $order Order */
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return;
        }
        if (
            $order->getCustomerId() &&
            ($order->getState() == Order::STATE_CLOSED || $order->getState() == Order::STATE_COMPLETE)
        ) {
            $this->processCreditProduct($order);
        }
    }

    /**
     * @param Order $order
     * @return void
     */
    public function processCreditProduct($order)
    {
        $credits = 0;
        /** @var Order\Item $item */
        foreach ($order->getAllItems() as $item) {
            if ($item->getProductType() == Type::TYPE_CREDITPOINTS ||
                $item->getRealProductType() == Type::TYPE_CREDITPOINTS
            ) {
                $productOptions = $item->getProductOptions();
                if (!isset($productOptions['info_buyRequest'])) {
                    continue;
                }
                $requestOptions = $productOptions['info_buyRequest'];
                $value  = 0;
                $option = $this->productOptionCreditFactory->create();
                if (!isset($requestOptions['creditOption'])) {
                    $option->getResource()->load(
                        $option, $requestOptions['product'], ProductOptionCreditInterface::KEY_OPTION_PRODUCT_ID
                    );
                } else {
                    $optionId = $requestOptions['creditOption'];
                    if (isset($requestOptions['creditOptionId'])) {
                        $value    = $requestOptions['creditOption'];
                        $optionId = $requestOptions['creditOptionId'];
                    }

                    $option->getResource()->load($option, $optionId);
                }

                $credits += $this->optionHelper->getOptionCredits($option, $value) * $item->getQtyInvoiced();
            }
        }
        if ($credits) {
            $balance = $this->creditHelper->getBalance($order->getCustomerId(), $order->getOrderCurrencyCode());
            $balance->setTransactionCurrencyCode($order->getOrderCurrencyCode());

            $baseCredits = $this->culculationHelper->convertToCurrency(
                $credits, $order->getOrderCurrencyCode(), $balance->getCurrencyCode(), $order->getStore()
            );
            $balance->addTransaction(
                $credits,
                $baseCredits,
                \Mirasvit\Credit\Model\Transaction::ACTION_PURCHASED,
                ['order' => $order]
             );
        }
    }
}
