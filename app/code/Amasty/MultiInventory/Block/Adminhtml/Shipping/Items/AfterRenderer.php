<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Block\Adminhtml\Shipping\Items;

use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Model\WarehouseFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Json\EncoderInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;

/**
 * Class AfterRenderer
 */
class AfterRenderer extends Template
{
    /**
     * @var Warehouse\Order\ItemFactory
     */
    private $itemFactory;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var ItemFactory
     */
    private $itemWh;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;

    /**
     * @var OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * AfterRenderer constructor.
     *
     * @param Context $context
     * @param Warehouse\Order\ItemFactory $itemFactory
     * @param WarehouseFactory $factory
     * @param ItemFactory $itemWh
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param EncoderInterface $jsonEncoder
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Amasty\MultiInventory\Model\Warehouse\Order\ItemFactory $itemFactory,
        WarehouseFactory $factory,
        ItemFactory $itemWh,
        ShipmentRepositoryInterface $shipmentRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        CreditmemoRepositoryInterface $creditmemoRepository,
        EncoderInterface $jsonEncoder,
        OrderItemRepositoryInterface $orderItemRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->itemFactory = $itemFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->itemWh = $itemWh;
        $this->shipmentRepository = $shipmentRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->getData('options');
    }

    /**
     * Add field for change items in order shipment, invoice and creditmemo
     *
     * @return string
     */
    public function jsonData()
    {
        $data = [];
        $orderId = $this->getRequest()->getParam('order_id');

        if (!$orderId) {
            $orderId = $this->getOrderIdFromRequest();
        }

        if ($orderId) {
            $collection = $this->itemFactory->create()->getCollection()->getDataOrder($orderId);

            foreach ($collection as $item) {
                $fields = $item->toArray();
                $idField = $fields['parent'];

                if (!$idField) {
                    $idField = $fields['item'];
                }
                $itemType = $this->orderItemRepository->get($idField)->getProductType();

                if ($fields['parent'] !== null
                    && $itemType !== Configurable::TYPE_CODE
                ) {
                    $data[$idField][$fields['item']] = [
                        'data' => $fields,
                        'list' => $this->prepareList($fields)
                    ];
                } else {
                    $data[$idField] = [
                        'data' => $fields,
                        'list' => $this->prepareList($fields)
                    ];
                }
            }
        }

        return $this->jsonEncoder->encode($data);
    }

    /**
     * @return int|null
     */
    protected function getOrderIdFromRequest()
    {
        $orderId = null;

        if ($this->getRequest()->getParam('shipment_id')) {
            $shipmentId = $this->getRequest()->getParam('shipment_id');
            $shipment = $this->shipmentRepository->get($shipmentId);
            $orderId = $shipment->getOrderId();
        } elseif ($this->getRequest()->getParam('invoice_id')) {
            $invoiceId = $this->getRequest()->getParam('invoice_id');
            $invoice = $this->invoiceRepository->get($invoiceId);
            $orderId = $invoice->getOrderId();
        } elseif ($this->getRequest()->getParam('creditmemo_id')) {
            $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
            $creditmemo = $this->creditmemoRepository->get($creditmemoId);
            $orderId = $creditmemo->getOrderId();
        }

        return $orderId;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    private function prepareList($fields) {
        $list = $this->itemWh->create()->getItems($fields['product']);

        foreach ($list as $whId => $whData) {
            if ($whData['qty'] < $fields['ship_qty']) {
                unset($list[$whId]);
            }
        }

        return $list;
    }
}
