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



namespace Mirasvit\Rma\Controller\Rma;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveMessage extends \Mirasvit\Rma\Controller\Rma
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Url
     */
    private $rmaUrl;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface
     */
    private $rmaOrderService;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\ShippingManagementInterface
     */
    private $shippingManagement;
    /**
     * @var \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface
     */
    private $messageAddManagement;

    /**
     * SaveMessage constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\Rma\Helper\Rma\Url $rmaUrl
     * @param \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService
     * @param \Mirasvit\Rma\Api\Service\Rma\ShippingManagementInterface $shippingManagement
     * @param \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddManagement
     * @param \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Mirasvit\Rma\Helper\Rma\Url $rmaUrl,
        \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface $rmaOrderService,
        \Mirasvit\Rma\Api\Service\Rma\ShippingManagementInterface $shippingManagement,
        \Mirasvit\Rma\Api\Service\Message\MessageManagement\AddInterface $messageAddManagement,
        \Mirasvit\Rma\Helper\Controller\Rma\StrategyFactory $strategyFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->registry             = $registry;
        $this->rmaRepository        = $rmaRepository;
        $this->rmaUrl               = $rmaUrl;
        $this->rmaOrderService      = $rmaOrderService;
        $this->shippingManagement   = $shippingManagement;
        $this->messageAddManagement = $messageAddManagement;
        $this->resultFactory        = $context->getResultFactory();
        $this->customerSession      = $customerSession;

        parent::__construct($strategyFactory, $customerSession, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequireCustomerAutorization()
    {
        return $this->strategy->isRequireCustomerAutorization();
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $rma = $this->strategy->initRma($this->getRequest());
            if (!$this->registry->registry('current_rma')) {
                $this->registry->register('current_rma', $rma);
            }
            $isConfirmShipping = $this->getRequest()->getParam('shipping_confirmation');
            /// we need to confirm shipping BEFORE posting message
            /// (message can be from custom variables value in the shipping confirmation dialog)
            if ($isConfirmShipping) {
                $data = $this->getRequest()->getParams();
                $this->shippingManagement->confirmShipping($rma, $data);
                $this->messageManager->addSuccessMessage(__('Shipping is confirmed. Thank you!'));
            }
            $message = $this->getRequest()->getParam('message');
            if (!($isConfirmShipping && !$message)) {
                $params = [
                    'isNotifyAdmin' => 1,
                    'isNotified'    => 0,
                ];
                try {
                    $performer = $this->strategy->getPerformer();
                } catch (InputException $e) {
                    $order = $this->rmaOrderService->getOrder($rma);
                    $orderId = $order ? $order->getId() : $rma->getOrderId();
                    $this->customerSession->setRMAGuestOrderId($orderId);
                    $this->customerSession->setRMAGuestOrderIsOffline($orderId);
                    $performer = $this->strategy->getPerformer();
                } catch (NoSuchEntityException $e) {
                    $this->messageManager->addErrorMessage(
                        __('An error occurred while loading order information.')
                    );
                    return $resultRedirect->setUrl($this->strategy->getRmaUrl($rma));
                }
                $this->messageAddManagement->addMessage(
                    $performer,
                    $rma,
                    $message,
                    $params
                );
                $this->customerSession->setRMAGuestOrderId(null);
                $this->customerSession->setRMAGuestOrderIsOffline(null);
            }

            if (!$isConfirmShipping) {
                $this->messageManager->addSuccessMessage(__('Your message was successfully added'));
            }

            return $resultRedirect->setUrl($this->strategy->getRmaUrl($rma));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/view', ['id' => $rma->getGuestId()]);
        }
    }
}
