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



namespace Mirasvit\Rma\Helper\Controller\Rma;

use Mirasvit\Rma\Api\Config\OfflineOrderConfigInterface;

class StrategyFactory
{

    /**
     * @var NoAccessStrategy
     */
    private $noAccessStrategy;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var GuestStrategy
     */
    private $guestStrategy;
    /**
     * @var CustomerStrategy
     */
    private $customerStrategy;

    /**
     * StrategyFactory constructor.
     * @param CustomerStrategy $customerStrategy
     * @param GuestStrategy $guestStrategy
     * @param NoAccessStrategy $noAccessStrategy
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Controller\Rma\CustomerStrategy $customerStrategy,
        \Mirasvit\Rma\Helper\Controller\Rma\GuestStrategy $guestStrategy,
        \Mirasvit\Rma\Helper\Controller\Rma\NoAccessStrategy $noAccessStrategy,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerStrategy = $customerStrategy;
        $this->guestStrategy    = $guestStrategy;
        $this->customerSession  = $customerSession;
        $this->noAccessStrategy = $noAccessStrategy;
    }


    /**
     * @param \Magento\Framework\App\RequestInterface|false $request
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return AbstractStrategy
     */
    public function create($request = false)
    {
        try {
            if ($this->customerSession->getId()) {
                return $this->customerStrategy;
            } elseif (
                $this->isOfflineGuest() ||
                $this->customerSession->getRMAGuestOrderId() ||
                ($request && $this->guestStrategy->initRma($request))
            ) {
                return $this->guestStrategy;
            } else {
                return $this->noAccessStrategy;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noAccessStrategy;
        }
    }

    /**
     * @return bool
     */
    private function isOfflineGuest()
    {
        return $this->customerSession->getRMAGuestOrderId() == OfflineOrderConfigInterface::OFFLINE_ORDER_PLACEHOLDER &&
            !empty($this->customerSession->getRMAEmail());
    }
}