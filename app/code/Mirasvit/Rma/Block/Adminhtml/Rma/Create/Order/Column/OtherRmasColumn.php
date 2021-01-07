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


namespace Mirasvit\Rma\Block\Adminhtml\Rma\Create\Order\Column;

use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Sales\Model\OrderRepository;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaOrderInterface;

class OtherRmasColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var RmaManagementInterface
     */
    private $rmaManagementService;
    /**
     * @var RmaOrderInterface
     */
    private $rmaOrder;

    /**
     * OtherRmasColumn constructor.
     * @param OrderRepository $orderRepository
     * @param RmaManagementInterface $rmaManagementService
     * @param RmaOrderInterface $rmaOrder
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        OrderRepository $orderRepository,
        RmaManagementInterface $rmaManagementService,
        RmaOrderInterface $rmaOrder,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->orderRepository = $orderRepository;
        $this->rmaManagementService = $rmaManagementService;
        $this->rmaOrder = $rmaOrder;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $html = '';
        $orderId = $row['entity_id'];
        $order = $this->orderRepository->get($orderId);
        $rmas = $this->rmaManagementService->getRmasByOrder($order);
        $rmaIds = [];
        foreach ($rmas as $rma) {
            $orders = $this->rmaOrder->getOrders($rma);
            foreach ($orders as $order) {
                if (!in_array($rma->getId(), $rmaIds) && !$order->getIsOffline()) {
                    $url  = $this->getUrl('rma/rma/edit', ['id' => $rma->getId()]);
                    $html .= '<a href="' . $url . '" target="_blank">#' . $this->escapeHtml($rma->getIncrementId()) . '</a><br/>';
                    $rmaIds[] = $rma->getId();
                }
            }
        }

        return $html;
    }
}