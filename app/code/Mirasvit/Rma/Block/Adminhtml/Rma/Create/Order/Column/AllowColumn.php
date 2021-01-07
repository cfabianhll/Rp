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
use Mirasvit\Rma\Api\Service\Item\ItemListBuilderInterface;
use Mirasvit\Rma\Api\Service\Item\ItemManagement\QuantityInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;

class AllowColumn extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var ItemListBuilderInterface
     */
    private $itemListBuilder;
    /**
     * @var QuantityInterface
     */
    private $itemQuantityManagement;
    /**
     * @var RmaManagementInterface
     */
    private $rmaManagementService;

    /**
     * AllowColumn constructor.
     * @param OrderRepository $orderRepository
     * @param ItemListBuilderInterface $itemListBuilder
     * @param QuantityInterface $itemQuantityManagement
     * @param RmaManagementInterface $rmaManagementService
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        OrderRepository $orderRepository,
        ItemListBuilderInterface $itemListBuilder,
        QuantityInterface $itemQuantityManagement,
        RmaManagementInterface $rmaManagementService,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->orderRepository = $orderRepository;
        $this->itemListBuilder = $itemListBuilder;
        $this->itemQuantityManagement = $itemQuantityManagement;
        $this->rmaManagementService = $rmaManagementService;
    }

    /**
     * get category name
     * @param  DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $allow = false;
        $orderId = $row['entity_id'];
        $order = $this->orderRepository->get($orderId);
        $rmas = $this->rmaManagementService->getRmasByOrder($order);
        foreach ($rmas as $rma) {
            $items = $this->itemListBuilder->getRmaItems($rma);
            foreach ($items as $item) {
                if (!$item->getIsOffline()) {
                    $qty = $this->itemQuantityManagement->getQtyAvailable($item);
                    if ($qty) {
                        $allow = true;
                        break;
                    }
                }
            }
        }
        $row->setRmaAllows($allow || !count($rmas));
        if ($row->getRmaAllows()) {
            $html = '<span style="color: green">Yes</span>';
        } else {
            $html = '<span style="color: red">No</span>';
        }

        return $html;
    }
}