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

class Subscribe extends \Mirasvit\Credit\Controller\Account
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $balance      = $this->balanceFactory->create()->loadByCustomer(
            $this->_getSession()->getCustomer(),
            $this->storeManager->getStore()->getCurrentCurrencyCode()
        );
        $isSubscribed = (bool)$this->getRequest()->getParam('is_subscribed');

        $balance->setIsSubscribed($isSubscribed)->save();

        $this->messageManager->addSuccessMessage(__('Email subscription was successfully updated.'));

        $this->_redirect('*/*/');
    }
}
