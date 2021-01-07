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



namespace Mirasvit\Rma\Controller\Guest;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;

class Offline extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface
     */
    private $offlineOrderConfig;
    /**
     * @var \Mirasvit\Rma\Api\Service\Order\LoginInterface
     */
    private $orderLoginService;

    /**
     * Offline constructor.
     * @param \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface $offlineOrderConfig
     * @param \Mirasvit\Rma\Api\Service\Order\LoginInterface $orderLoginService
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface $offlineOrderConfig,
        \Mirasvit\Rma\Api\Service\Order\LoginInterface $orderLoginService,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->offlineOrderConfig = $offlineOrderConfig;
        $this->orderLoginService = $orderLoginService;
        $this->customerSession = $customerSession;
        $this->resultFactory = $context->getResultFactory();
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        //we need this for demo purposes
        if ($this->getRequest()->getParam('c') !== null) {
            $this->customerSession->logout();
        }
        if ($this->customerSession->isLoggedIn() || !$this->offlineOrderConfig->isOfflineOrdersEnabled()) {
            return $resultRedirect->setPath('returns/rma/new');
        }
        try {
            $firstname = $this->getRequest()->getParam('customer_firstname');
            $lastname  = $this->getRequest()->getParam('customer_lastname');
            $email = trim($this->getRequest()->getParam('email'));
            if (\Zend_Validate::is($email, 'EmailAddress')) {
                $this->customerSession->setRMAFirstname(strip_tags($firstname));
                $this->customerSession->setRMALastname(strip_tags($lastname));
                $this->customerSession->setRMAEmail($email);
                $this->customerSession->setRMAGuestOrderId('offline');

                return $resultRedirect->setPath('returns/rma/new', ['order_id' => 'offline']);
            } elseif ($email) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Incorrect email format'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
