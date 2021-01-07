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
use Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface;

class NewAction extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Mirasvit\Rma\Api\Service\Order\LoginInterface
     */
    private $orderLoginService;
    /**
     * @var RmaPolicyConfigInterface
     */
    private $rmaPolicyConfig;

    /**
     * NewAction constructor.
     * @param \Mirasvit\Rma\Api\Service\Order\LoginInterface $orderLoginService
     * @param RmaPolicyConfigInterface $rmaPolicyConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Order\LoginInterface $orderLoginService,
        RmaPolicyConfigInterface $rmaPolicyConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->orderLoginService = $orderLoginService;
        $this->rmaPolicyConfig   = $rmaPolicyConfig;
        $this->customerSession   = $customerSession;
        $this->resultFactory     = $context->getResultFactory();

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
        if ($this->customerSession->isLoggedIn()) {
            return $resultRedirect->setPath('returns/rma/new');
        }
        try {
            $order = $this->orderLoginService->getOrder(
                $this->getRequest()->getParam('order_increment_id'),
                $this->getRequest()->getParam('email')
            );
            if ($order) {
                if (!$order->getIsOffline() &&
                    !in_array($order->getStatus(), $this->rmaPolicyConfig->getAllowRmaInOrderStatuses())
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You have no completed orders to request RMA or your orders were placed more than %1 days ago. Please, contact customer service.',
                            $this->rmaPolicyConfig->getReturnPeriod()
                        )
                    );
                }
                $this->customerSession->setRMAGuestOrderId($order->getId());
                $this->customerSession->setRMAGuestOrderIsOffline($order->getIsOffline());
                return $resultRedirect->setPath('returns/rma/list');
            } elseif ($this->getRequest()->getParam('order_increment_id')) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Wrong Order #, Email'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
