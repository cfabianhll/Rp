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



namespace Mirasvit\Rma\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Mirasvit\Rma\Repository\OrderStatusHistoryRepository
     */
    private $orderStatusHistoryRepository;
    /**
     * @var \Mirasvit\Rma\Model\OrderStatusHistoryFactory
     */
    private $orderStatusHistoryFactory;

    /**
     * OrderSaveAfter constructor.
     * @param \Mirasvit\Rma\Model\OrderStatusHistoryFactory $orderStatusHistoryFactory
     * @param \Mirasvit\Rma\Repository\OrderStatusHistoryRepository $orderStatusHistoryRepository
     */
    public function __construct(
        \Mirasvit\Rma\Model\OrderStatusHistoryFactory $orderStatusHistoryFactory,
        \Mirasvit\Rma\Repository\OrderStatusHistoryRepository $orderStatusHistoryRepository
    ) {
        $this->orderStatusHistoryFactory    = $orderStatusHistoryFactory;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$order = $observer->getEvent()->getOrder()) {
            return;
        }
        /** @var \Magento\Sales\Model\Order $order */
        $status = $order->getStatus();
        $historyStatus = $this->orderStatusHistoryRepository->getByOrderId($order->getId());

        if ($status != $historyStatus->getStatus()) {
            $historyStatus->setOrderId($order->getId());
            $historyStatus->setStatus($status);
            $historyStatus->setCreatedAt($order->getUpdatedAt());
//            $historyStatus->setCreatedAt(strtotime($order->getUpdatedAt()));
            $this->orderStatusHistoryRepository->save($historyStatus);
        }
    }
}
