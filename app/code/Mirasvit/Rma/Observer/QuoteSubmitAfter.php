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

class QuoteSubmitAfter implements ObserverInterface
{
    /**
     * @var \Magento\Backend\Model\Session\Quote|mixed
     */
    private $quoteSession;
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $rmaFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * QuoteSubmitAfter constructor.
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Mirasvit\Rma\Model\RmaFactory $rmaFactory
     */
    public function __construct(
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Mirasvit\Rma\Model\RmaFactory $rmaFactory
    ) {
        $this->quoteSession  = $quoteSession;
        $this->objectManager = $objectManager;
        $this->rmaFactory    = $rmaFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        if ($rmaId = $this->quoteSession->getRmaId()) {
            /** @var \Mirasvit\Rma\Model\Rma $rma */
            $rma = $this->rmaFactory->create()->load($rmaId);
            $ids = $rma->getExchangeOrderIds();
            $ids[] = $order->getId();
            $rma->setExchangeOrderIds($ids);
            $rma->getResource()->save($rma);
            $this->quoteSession->unsetRmaId();
        }
    }
}
