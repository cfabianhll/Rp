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



namespace Mirasvit\Credit\Controller\Account;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\Credit\Model\Transaction;

class Send2friend extends \Mirasvit\Credit\Controller\Account
{
    /**
     *
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $email = $this->getRequest()->getParam('email');
        $amount = floatval($this->getRequest()->getParam('amount'));
        $message = $this->escaper->escapeHtml($this->getRequest()->getParam('message'));
        $notify = $this->getRequest()->getParam('notify');

        if ($email && $amount > 0) {
            $customer = $this->_getSession()->getCustomer();
            $store = $this->storeManager->getStore();

            $friend = $this->customerFactory->create();
            $friend->setWebsiteId($this->storeManager->getWebsite()->getId());
            $friend->loadByEmail($email);

            $error = '';
            if (!$friend->getId()) {
                $error = __('Customer with email %1 does not exist.', $email);
            }
            if ($friend->getId() == $customer->getId()) {
                $error = __('You can not send credits to yourself');
            }

            if (!$error) {
                $currencyCode = $store->getCurrentCurrencyCode();
                $friendBalance = $this->balanceFactory->create()->loadByCustomer($friend, $currencyCode);
                $myBalance = $this->balanceFactory->create()->loadByCustomer($customer, $currencyCode);
                $friendBalance->setTransactionCurrencyCode($currencyCode);
                $myBalance->setTransactionCurrencyCode($currencyCode);

                $baseAmount = $this->calculationHelper->convertToCurrency(
                    $amount, $currencyCode, $myBalance->getCurrencyCode(), $store
                );

                if ($myBalance->getAmount() >= $baseAmount) {
                    $myBalance->addTransaction(
                        -1 * $amount,
                        -1 * $baseAmount,
                        Transaction::ACTION_USED,
                        __('Send to friend %1', $email)
                    );

                    $baseAmount = $this->calculationHelper->convertToCurrency(
                        $amount, $currencyCode, $friendBalance->getCurrencyCode(), $store
                    );

                    $friendBalance->addTransaction(
                        $amount,
                        $baseAmount,
                        Transaction::ACTION_MANUAL,
                        __(
                            'Received from %1 %2',
                            $this->_getSession()->getCustomer()->getEmail(),
                            $message
                        ),
                        $notify
                    );

                    $this->messageManager->addSuccessMessage(__('Balance successfully sent.'));

                    $this->customerSession->setSend2FriendFormData(false);
                } else {
                    $this->customerSession->setSend2FriendFormData($this->getRequest()->getParams());

                    $this->messageManager->addErrorMessage(__('Amount cannot exceed balance amount.'));
                }
            } else {
                $this->customerSession->setSend2FriendFormData($this->getRequest()->getParams());

                $this->messageManager->addErrorMessage($error);
            }
        }

        $this->_redirect('*/*/');
    }
}
