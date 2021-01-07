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



namespace Mirasvit\Rma\Helper\Order;

use Mirasvit\Rma\Model\Resolution;

/**
 * Helper for CreditMome
 */
class Creditmemo extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    private $backendUrlManager;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection
     */
    private $invoiceCollection;
    /**
     * @var \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface
     */
    private $resolutionManagement;
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    private $creditmemoModel;
    /**
     * @var \Magento\Sales\Api\CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;
    /**
     * @var \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface
     */
    private $itemListBuilder;

    /**
     * Creditmemo constructor.
     * @param \Magento\Backend\Model\Url $backendUrlManager
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemoModel
     * @param \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection
     * @param \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface $resolutionManagement
     * @param \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Backend\Model\Url $backendUrlManager,
        \Magento\Sales\Model\Order\Creditmemo $creditmemoModel,
        \Magento\Sales\Api\CreditmemoRepositoryInterface $creditmemoRepository,
        \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection $invoiceCollection,
        \Mirasvit\Rma\Api\Service\Resolution\ResolutionManagementInterface $resolutionManagement,
        \Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface $itemListBuilder,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->backendUrlManager    = $backendUrlManager;
        $this->moduleManager        = $context->getModuleManager();
        $this->creditmemoModel      = $creditmemoModel;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->invoiceCollection    = $invoiceCollection;
        $this->resolutionManagement = $resolutionManagement;
        $this->itemListBuilder      = $itemListBuilder;

        parent::__construct($context);
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma    $rma
     * @param \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order
     *
     * @return bool
     */
    public function canCreateCreditmemo($rma, $order)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if (!$order->canCreditmemo()) {
            return false;
        }

        $creditModuleInstalled = $this->moduleManager->isEnabled('Mirasvit_Credit');
        if ($rma->getCreditMemoIds()) {
            foreach ($rma->getCreditMemoIds() as $id) {
                $creditmemo = $this->creditmemoRepository->get($id);
                if ($creditmemo->getOrderId() == $order->getId()) {
                    return false;
                }
            }
        }

        $this->creditmemoModel->setOrder($order);
        $realPaidAmount = $this->creditmemoModel->roundPrice($order->getTotalPaid() + $order->getCreditInvoiced());
        if ($creditModuleInstalled && $realPaidAmount > 0) {
            $realRefunded = $this->creditmemoModel->roundPrice(
                $order->getTotalRefunded() + $order->getCreditTotalRefunded()
            );
            if (abs($realPaidAmount - $realRefunded) < .0001) {
                return false;
            }
        }

        return $this->resolutionManagement->isCreditmemoAllowed($rma);
    }

    /**
     * @param \Mirasvit\Rma\Model\Rma    $rma
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return string
     */
    public function getCreditmemoUrl($rma, $order)
    {
        $collection = $this->invoiceCollection->addFieldToFilter('order_id', $order->getId());

        if ($collection->count() == 1) {
            $invoice = $collection->getFirstItem();

            return $this->backendUrlManager->getUrl(
                'sales/order_creditmemo/start',
                ['order_id' => $order->getId(), 'invoice_id' => $invoice->getId(), 'rma_id' => $rma->getId()]
            );
        } else {
            return $this->backendUrlManager->getUrl(
                'sales/order_creditmemo/start',
                ['order_id' => $order->getId(), 'rma_id' => $rma->getId()]
            );
        }
    }

}