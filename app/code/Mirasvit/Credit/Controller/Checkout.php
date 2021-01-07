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



namespace Mirasvit\Credit\Controller;

use Magento\Checkout\Model\Cart as CustomerCart;
use Mirasvit\Credit\Model\Config;

abstract class Checkout extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $cartFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;
    /**
     * @var \Mirasvit\Credit\Helper\Data
     */
    private $creditHelper;

    /**
     * @param \Mirasvit\Credit\Helper\Data                       $creditHelper
     * @param \Magento\Checkout\Model\CartFactory                $cartFactory
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator     $formKeyValidator
     * @param CustomerCart                                       $cart
     * @codeCoverageIgnore
     */
    public function __construct(
        \Mirasvit\Credit\Helper\Data $creditHelper,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart
    ) {
        $this->creditHelper = $creditHelper;
        $this->cartFactory = $cartFactory;
        $this->customerSession = $customerSession;
        $this->resultFactory = $context->getResultFactory();

        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * @return void
     */
    protected function _processPost()
    {
        $isUseCredit = true;

        if ($this->getRequest()->getParam('remove-credit') == 1) {
            $isUseCredit = false;
        }
        $creditAmount = 0;
        if (!empty($this->getRequest()->getParam('credit-amount'))) {
            $creditAmount = (float)$this->getRequest()->getParam('credit-amount');

            $balanceAmount = $this->creditHelper->getBalance()->getAmount();
            if ($creditAmount > $balanceAmount) {
                $creditAmount = $balanceAmount;
            }
        }

        $quote = $this->cartFactory->create()->getQuote();
        $quote->setUseCredit($isUseCredit ? Config::USE_CREDIT_YES : Config::USE_CREDIT_NO)
            ->setBaseCreditAmountUsed(0)
            ->setCreditAmountUsed(0)
            ->setManualUsedCredit($creditAmount)
            ->collectTotals()
            ->save();
    }
}
