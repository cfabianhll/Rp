<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Model\Checkout;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\AdminOrder\EmailSender;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\AddressFactory;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Tax\ItemFactory;
use Magento\Sales\Model\Order\TaxFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\GrandTotalDetailsInterfaceFactory;
use Magestore\Appadmin\Api\Event\DispatchServiceInterface;
use Magestore\Webpos\Api\Checkout\CheckoutRepositoryInterface;
use Magestore\Webpos\Api\Checkout\PosOrderRepositoryInterface;
use Magestore\Webpos\Api\Data\Checkout\Order\AddressInterface;
use Magestore\Webpos\Api\Data\Checkout\Order\Item\TaxInterface;
use Magestore\Webpos\Api\Data\Checkout\Order\ItemInterface;
use Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterface;
use Magestore\Webpos\Api\Data\Checkout\Order\PaymentInterfaceFactory;
use Magestore\Webpos\Api\MultiSourceInventory\StockManagementInterface;
use Magestore\Webpos\Api\Sales\Order\InvoiceRepositoryInterface;
use Magestore\Webpos\Api\Sales\Order\ShipmentRepositoryInterface;
use Magestore\Webpos\Api\Staff\SessionRepositoryInterface;
use Magestore\Webpos\Helper\Data;
use Magestore\Webpos\Log\Logger;
use Magestore\Webpos\Model\Checkout\Order\Payment;
use Magestore\Webpos\Model\Checkout\Order\Payment\Error as OrderPaymentError;
use Magestore\Webpos\Api\Sales\OrderManagement\AppendReservationsAfterOrderPlacementInterface;
use Magestore\Webpos\Model\Request\ActionLog;
use Magestore\Webpos\Model\ResourceModel\Request\ActionLog\CollectionFactory;
use Magestore\Webpos\Model\Sales\OrderRepository;

/**
 * Class PosOrderRepository
 *
 * Used for pos order repository
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PosOrderRepository implements PosOrderRepositoryInterface
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $customerAddress;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var OrderPaymentInterfaceFactory
     */
    protected $orderPaymentInterfaceFactory;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    protected $invoiceRepositoryInterface;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepositoryInterface;

    /**
     * @var EmailSender
     */
    protected $emailSender;

    /**
     * @var PaymentInterfaceFactory
     */
    protected $paymentOrderInterfaceFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SessionRepositoryInterface
     */
    protected $sessionRepository;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\CatalogInventory\Api\StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $stockIndexerProcessor;

    /**
     * @var Processor
     */
    protected $priceIndexer;

    /**
     * @var \Magestore\Webpos\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item
     */
    protected $resourceStock;

    /**
     * @var StockManagementInterface
     */
    protected $MSIStockManagement;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var GrandTotalDetailsInterfaceFactory
     */
    protected $detailsFactory;

    /**
     * @var ItemFactory
     */
    protected $itemTaxFactory;

    /**
     * @var PosOrderFactory
     */
    protected $posOrderFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ServiceInputProcessor
     */
    protected $serviceInputProcessor;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CollectionFactory
     */
    protected $actionLogCollectionFactory;

    protected $currentStoreId = '';
    protected $parentItemId = [];
    protected $itemProductId = [];

    /**
     * PosOrderRepository constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param AddressFactory $addressFactory
     * @param \Magento\Customer\Model\Address $customerAddress
     * @param Customer $customer
     * @param OrderPaymentInterfaceFactory $orderPaymentInterfaceFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param StoreManagerInterface $storeManager
     * @param StoreFactory $storeFactory
     * @param OrderRepository $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepositoryInterface
     * @param ShipmentRepositoryInterface $shipmentRepositoryInterface
     * @param EmailSender $emailSender
     * @param PaymentInterfaceFactory $paymentOrderInterfaceFactory
     * @param Registry $coreRegistry
     * @param RequestInterface $request
     * @param SessionRepositoryInterface $sessionRepository
     * @param ManagerInterface $_eventManager
     * @param Data $helper
     * @param \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param Processor $priceIndexer
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $resourceStock
     * @param \Magestore\Webpos\Helper\Order $orderHelper
     * @param StockManagementInterface $MSIStockManagement
     * @param OrderManagementInterface $orderManagement
     * @param TaxFactory $detailsFactory
     * @param ItemFactory $itemTaxFactory
     * @param ObjectManagerInterface $objectManager
     * @param PosOrderFactory $posOrderFactory
     * @param Logger $logger
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param Manager $moduleManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory $actionLogCollectionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        AddressFactory $addressFactory,
        \Magento\Customer\Model\Address $customerAddress,
        Customer $customer,
        OrderPaymentInterfaceFactory $orderPaymentInterfaceFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        StoreManagerInterface $storeManager,
        StoreFactory $storeFactory,
        OrderRepository $orderRepository,
        InvoiceRepositoryInterface $invoiceRepositoryInterface,
        ShipmentRepositoryInterface $shipmentRepositoryInterface,
        EmailSender $emailSender,
        PaymentInterfaceFactory $paymentOrderInterfaceFactory,
        Registry $coreRegistry,
        RequestInterface $request,
        SessionRepositoryInterface $sessionRepository,
        ManagerInterface $_eventManager,
        Data $helper,
        \Magento\CatalogInventory\Api\StockManagementInterface $stockManagement,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        Processor $priceIndexer,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $resourceStock,
        \Magestore\Webpos\Helper\Order $orderHelper,
        StockManagementInterface $MSIStockManagement,
        OrderManagementInterface $orderManagement,
        TaxFactory $detailsFactory,
        ItemFactory $itemTaxFactory,
        ObjectManagerInterface $objectManager,
        PosOrderFactory $posOrderFactory,
        Logger $logger,
        ServiceInputProcessor $serviceInputProcessor,
        Manager $moduleManager,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $actionLogCollectionFactory
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
        $this->customerAddress = $customerAddress;
        $this->customer = $customer;
        $this->orderPaymentInterfaceFactory = $orderPaymentInterfaceFactory;
        $this->orderItemFactory = $orderItemFactory;
        $this->storeManager = $storeManager;
        $this->storeFactory = $storeFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepositoryInterface = $invoiceRepositoryInterface;
        $this->shipmentRepositoryInterface = $shipmentRepositoryInterface;
        $this->emailSender = $emailSender;
        $this->paymentOrderInterfaceFactory = $paymentOrderInterfaceFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->request = $request;
        $this->sessionRepository = $sessionRepository;
        $this->_eventManager = $_eventManager;
        $this->helper = $helper;
        $this->stockManagement = $stockManagement;
        $this->stockIndexerProcessor = $stockIndexerProcessor;
        $this->priceIndexer = $priceIndexer;
        $this->resourceStock = $resourceStock;
        $this->orderHelper = $orderHelper;
        $this->MSIStockManagement = $MSIStockManagement;
        $this->orderManagement = $orderManagement;
        $this->objectManager = $objectManager;
        $this->detailsFactory = $detailsFactory;
        $this->itemTaxFactory = $itemTaxFactory;
        $this->posOrderFactory = $posOrderFactory;
        $this->logger = $logger;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->moduleManager = $moduleManager;
        $this->customerRepository = $customerRepository;
        $this->actionLogCollectionFactory = $actionLogCollectionFactory;
    }

    /**
     * Process convert order
     *
     * @param string $incrementId
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface|bool
     * @throws \Exception
     */
    public function processConvertOrder($incrementId)
    {
        /** @var PosOrder $posOrder */
        $posOrder = $this->posOrderFactory->create();
        $posOrder->load($incrementId, 'increment_id');

        if (!$posOrder->getId()) {
            return false;
        }

        if ($posOrder->getStatus() == PosOrder::STATUS_PENDING
            || $posOrder->getStatus() == PosOrder::STATUS_FAILED) {
            // Convert to magento order
            try {
                return $this->convertPosOrderToMagentoOrder($posOrder);
            } catch (\Exception $e) {
                $this->logger->info($incrementId);
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
                $this->logger->info('___________________________________________');
                $posOrder->setStatus(PosOrder::STATUS_FAILED)->save();
            }
        } elseif ($posOrder->getStatus() == PosOrder::STATUS_PROCESSING) {
            return $this->processInCompletedOrder($posOrder);
        }

        try {
            /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $existedOrder */
            $existedOrder = $this->orderRepository->getWebposOrderByIncrementId($posOrder->getIncrementId());
        } catch (\Exception $e) {
            $existedOrder = false;
        }
        if ($existedOrder) {
            return $this->verifyOrderReturn($existedOrder);
        } else {
            throw new LocalizedException(
                __('Some things went wrong when trying to create new order!'),
                new \Exception(),
                DispatchServiceInterface::EXCEPTION_CODE_SAVED_REQUEST_TO_SERVER
            );
        }
    }

    /**
     * Convert order
     *
     * @param PosOrder $posOrder
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface|bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function convertPosOrderToMagentoOrder(PosOrder $posOrder)
    {
        try {
            /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $existedOrder */
            $existedOrder = $this->orderRepository->getWebposOrderByIncrementId($posOrder->getIncrementId());
        } catch (\Exception $e) {
            $existedOrder = false;
        }
        if ($existedOrder) {
            // Order has already existed
            $posOrder->setStatus(PosOrder::STATUS_COMPLETED)->save();
            return $this->verifyOrderReturn($existedOrder);
        }

        // If request order does NOT include store data or session data
        // then stopping convert order
        if (!$posOrder->getSessionId() || !$posOrder->getStoreId()) {
            $posOrder->setStatus(PosOrder::STATUS_FAILED)->save();
            return false;
        }
        $this->setCurrentStoreId($posOrder->getStoreId());

        // Modify request params
        $requestParams = $this->request->getParams();
//        $requestParams[RequestProcessor::SESSION_PARAM_KEY] = $posOrder->getSessionId();
        $requestParams[PosOrder::PARAM_ORDER_LOCATION_ID] = $posOrder->getLocationId();
        $requestParams[PosOrder::PARAM_ORDER_POS_ID] = $posOrder->getPosId();
        $hasFulfil = $this->moduleManager->isEnabled('Magestore_FulfilSuccess');
        if ($hasFulfil) {
            $requestParams['skip_check_picking'] = true;
        }
        $this->request->setParams($requestParams);

        // Convert array to object parameter
        $params = json_decode($posOrder->getParams(), true);
        $params = $this->serviceInputProcessor->process(
            CheckoutRepositoryInterface::class,
            'placeOrder',
            $params
        );
        $params = $this->modifyPlaceOrderParams($params);
        $order = $params[0];
        $createShipment = $params[1];
        $createInvoice = $params[2];
        // End Convert array to object parameter

        // Check customer_id in order data
        $order = $this->modifyOrderData($order);

        if (!$order) {
            $posOrder->setStatus(PosOrder::STATUS_FAILED)->save();
            return false;
        }

        $newOrder = $this->createOrder($order);
        $this->_eventManager->dispatch(
            'pos_sales_order_place_after',
            ['order' => $newOrder, 'data' => $order, 'shipment' => $createShipment, 'invoice' => $createInvoice]
        );
        $this->_eventManager->dispatch('sales_order_place_after', ['order' => $newOrder]);
        $this->appendReservationsAfterOrderPlacement($newOrder);
        $this->subtractOrderInventory($newOrder);

        // Check process shipment and invoice
        $posOrderAdditionalData = $this->processInvoiceAndShipment($newOrder, $createShipment, $createInvoice);

        // create webpos order payment
        if ($order->getPayments()) {
            $this->createWebposOrderPayment($newOrder, $order->getPayments());
        }

        if ($order->getOsPosCustomDiscountReason()) {
            $history = $newOrder->addStatusHistoryComment($order->getOsPosCustomDiscountReason());
            $history->save();
        }

        if ($order->getStatusHistories()) {
            foreach ($order->getStatusHistories() as $statusHistories) {
                $this->orderRepository->commentOrder($newOrder->getIncrementId(), $statusHistories);
            }
        }

        if ($this->helper->getStoreConfig('webpos/checkout/automatically_send_mail')) {
            $this->sendEmailOrder($newOrder);
        }

        $newOrder = $this->orderRepository->get($newOrder->getId());

        // Process increase customer sales rule usage
        // @codingStandardsIgnoreStart
        if (class_exists('Magento\SalesRule\Model\Coupon\UpdateCouponUsages')) {
            /** @var \Magento\Sales\Model\Order $currentOrder */
            $currentOrder = $this->orderFactory->create();
            $currentOrder->load($newOrder->getId());
            if ($currentOrder) {
                $updateCouponUsage = $this->objectManager->create('Magento\SalesRule\Model\Coupon\UpdateCouponUsages');
                $updateCouponUsage->execute($currentOrder, true);
            }
        }
        // @codingStandardsIgnoreEnd

        if (!count($posOrderAdditionalData)) {
            $posOrder->setStatus(PosOrder::STATUS_COMPLETED)->save();
        } else {
            $posOrder->setStatus(PosOrder::STATUS_PROCESSING)
                ->setAdditionalData(json_encode($posOrderAdditionalData))
                ->save();
        }

        // Process action log of the created order
        $this->processActionLogOfCreatedOrder($newOrder->getIncrementId());

        return $this->verifyOrderReturn($newOrder);
    }

    /**
     * Process action log of created order
     *
     * @param string $increment_id
     */
    public function processActionLogOfCreatedOrder($increment_id)
    {
        /** @var \Magestore\Webpos\Model\ResourceModel\Request\ActionLog\Collection $collection */
        $collection = $this->actionLogCollectionFactory->create();
        $collection->addFieldToFilter('order_increment_id', $increment_id);
        $collection->addFieldToFilter(
            'status',
            ['neq' => ActionLog::STATUS_COMPLETED]
        );

        $collection->processActionLog();
    }

    /**
     * Process in-completed order
     *
     * @param PosOrder $posOrder
     * @return bool
     */
    public function processInCompletedOrder(PosOrder $posOrder)
    {
        // Get current order
        try {
            /** @var \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order */
            $order = $this->orderRepository->getWebposOrderByIncrementId($posOrder->getIncrementId());
        } catch (\Exception $e) {
            $order = false;
        }
        if (!$order) {
            // Order has NOT existed
            $posOrder->setStatus(PosOrder::STATUS_FAILED)->save();
            return false;
        }

        $additionalData = $posOrder->getAdditionalData();
        if (!$additionalData) {
            $posOrder->setStatus(PosOrder::STATUS_COMPLETED)->save();
            return true;
        }
        $additionalData = json_decode($additionalData, true);

        $posOrderAdditionalData = $this->processInvoiceAndShipment(
            $order,
            isset($additionalData['shipment']) ? $additionalData['shipment'] : 0,
            isset($additionalData['invoice']) ? $additionalData['invoice'] : 0
        );

        if (!count($posOrderAdditionalData)) {
            $posOrder->setStatus(PosOrder::STATUS_COMPLETED)
                ->setAdditionalData('')
                ->save();
        } else {
            $posOrder->setStatus(PosOrder::STATUS_PROCESSING)
                ->setAdditionalData(json_encode($posOrderAdditionalData))
                ->save();
        }

        return true;
    }

    /**
     * Process shipment and invoice
     *
     * @param \Magento\Sales\Model\Order|\Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     * @param int $createShipment
     * @param int $createInvoice
     * @return array
     */
    public function processInvoiceAndShipment($order, $createShipment, $createInvoice)
    {
        $posOrderAdditionalData = [];
        if ($createShipment) {
            try {
                $this->shipmentRepositoryInterface->createShipmentByOrderId($order->getId());
            } catch (\Exception $e) {
                $this->logger->info($order->getIncrementId());
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
                $this->logger->info('___________________________________________');
                $posOrderAdditionalData['shipment'] = 1;
            }
        }
        if ($createInvoice) {
            try {
                $this->invoiceRepositoryInterface->createInvoiceByOrderId($order->getId());
            } catch (\Exception $e) {
                $this->logger->info($order->getIncrementId());
                $this->logger->info($e->getMessage());
                $this->logger->info($e->getTraceAsString());
                $this->logger->info('___________________________________________');
                $posOrderAdditionalData['invoice'] = 1;
            }
        }

        return $posOrderAdditionalData;
    }

    /**
     * Modify order data
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface|bool
     * @throws LocalizedException
     */
    public function modifyOrderData($order)
    {
        if ($order->getCustomerId()
            && $order->getTmpCustomerId()
            && $order->getTmpCustomerId() == 'pos_' . $order->getCustomerId()) {
            // customer_id is a temporary id
            /** @var SearchCriteriaInterface $searchCriteria */
            $searchCriteria = $this->objectManager->create(SearchCriteriaInterface::class);
            /** @var FilterGroup $filterGroup */
            $filterGroup = $this->objectManager->create(FilterGroup::class);
            /** @var Filter $filter */
            $filter = $this->objectManager->create(Filter::class);
            $filter->setField('tmp_customer_id');
            $filter->setValue($order->getTmpCustomerId());

            $filterGroup->setFilters([$filter]);
            $searchCriteria->setFilterGroups([$filterGroup]);

            $customerList = $this->customerRepository->getList($searchCriteria)->getItems();

            if (!count($customerList)) {
                return false;
            }

            /** @var CustomerInterface $customer */
            $customer = $customerList[0];
            $customerId = $customer->getId();

            // Correct customer_id in order's data
            $order->setCustomerId($customerId);

            // Correct customer_id in addresses's data
            $addresses = [];
            foreach ($order->getAddresses() as $address) {
                $address->setCustomerId($customerId);
                $addresses[] = $address;
            }
            $order->setAddresses($addresses);
        }

        return $order;
    }

    /**
     * Create new order
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     * @return \Magento\Sales\Model\Order
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function createOrder($order)
    {
        $this->_coreRegistry->unregister('create_order_webpos');
        $this->_coreRegistry->register('create_order_webpos', true);
        $locationId = $this->request->getParam(PosOrder::PARAM_ORDER_LOCATION_ID);

        $this->_coreRegistry->unregister('current_location_id');
        $this->_coreRegistry->unregister('pos_fulfill_online');
        $this->_coreRegistry->register('current_location_id', $locationId);
        $this->_coreRegistry->register('pos_fulfill_online', $order->getPosFulfillOnline());

        $payments = $order->getPayments();
        $addresses = $order->getAddresses();
        $items = $order->getItems();

        $order->setEntityId('');
        $order->setQuoteAddressId('');
        $order->setBillingAddressId('');
        $order->setShippingAddressId('');

        /**
         * Check quote
         */
        $this->checkQuote($order);

        /** @var \Magento\Sales\Model\Order $newOrder */
        $newOrder = $this->orderFactory->create();
        try {
            $data = $order->getData();
            unset($data['items']);
            unset($data['payment']);
            unset($data['addresses']);
            unset($data['status_histories']);

            $newOrder->setData($data);

            // add address to order
            $this->addAddressToOrder($newOrder, $addresses);

            // add payment to order
            $this->addPaymentToOrder($newOrder, $payments);

            // add item to order
            $this->addItemToOrder($newOrder, $items);

            $storeId = $this->checkStoreId($newOrder->getStoreId());
            $newOrder->setStoreId($storeId);

            $newOrder->save();
            $this->afterCreateNewOrder($newOrder, $order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $newOrder;
    }

    /**
     * Remove quote id data if quote is not exist
     *
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $order
     */
    public function checkQuote(\Magestore\Webpos\Api\Data\Checkout\OrderInterface &$order)
    {
        $quoteId = $order->getQuoteId();
        $quote = $this->quoteFactory->create()->load($quoteId);
        if (!$quote->getId()) {
            $order->setQuoteId('');
        }
    }

    /**
     * Get correct status from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magestore\Webpos\Api\Data\Checkout\OrderInterface
     */
    public function verifyOrderReturn($order)
    {
        return $this->orderHelper->verifyOrderReturn($order);
    }

    /**
     * Add addresses
     *
     * @param \Magento\Sales\Model\Order $order
     * @param AddressInterface[] $addresses
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addAddressToOrder(&$order, $addresses)
    {
        foreach ($addresses as $address) {
            /** @var Address $add */
            $add = $this->addressFactory->create();
            $data = $address->getData();

            unset($data['entity_id']);
            unset($data['parent_id']);
            unset($data['quote_address_id']);
            $customerAddress = $this->customerAddress->load($data['customer_address_id']);
            if (!$customerAddress->getId()) {
                unset($data['customer_address_id']);
            }
            $customer = $this->customer->load($data['customer_id']);
            if (!$customer->getId()) {
                unset($data['customer_id']);
            }

            if (isset($data['street'][0]) && !$data['street'][0]) {
                $data['street'][0] = 'N/A';
            }
            if (isset($data['city']) && !$data['city']) {
                $data['city'] = 'N/A';
            }
            if (isset($data['region']) && !$data['region']) {
                $data['region'] = 'N/A';
            }
            if (isset($data['telephone']) && !$data['telephone']) {
                $data['telephone'] = 'N/A';
            }

            if ($address->getAddressType() == Address::TYPE_BILLING) {
                $add->setData($data);
                $order->setBillingAddress($add);
            } else {
                $add->setData($data);
                $add->setAddressType(Address::TYPE_SHIPPING);
                $order->setShippingAddress($add);
            }
        }
    }

    /**
     * Add payments
     *
     * @param \Magento\Sales\Model\Order $order
     * @param PaymentInterface[] $payments
     * @param boolean $reCalculateTotal
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addPaymentToOrder(&$order, $payments, $reCalculateTotal = false)
    {
        if ($order->getId()) {
            $oldOrder = $this->orderFactory->create()->load($order->getId());
        }
        /** @var OrderPaymentInterface $pm */
        $pm = $this->orderPaymentInterfaceFactory->create();
        $pm->setMethod('multipaymentforpos');
        $pm->setAmountOrdered($order->getGrandTotal());
        $pm->setBaseAmountOrdered($order->getBaseGrandTotal());
        $pm->setAmountPaid(0);
        $pm->setBaseAmountPaid(0);
        $pm->setBaseShippingAmount(0);
        $pm->setShippingAmount(0);
        $totalPaid = 0;
        $baseTotalPaid = 0;
        $takeBaseAmount = 0;
        $takeAmount = 0;
        $posChange = 0;
        $basePosChange = 0;
        $paymentsReferenceNumber = '';
        if (!empty($payments) && count($payments)) {
            foreach ($payments as $payment) {
                if ($payment->getReferenceNumber()) {
                    $paymentsReferenceNumber .= ',' . $payment->getReferenceNumber();
                }
                if ($payment->getIsPayLater()) {
                    continue;
                }
                $totalPaid += (float)$payment->getAmountPaid();
                $baseTotalPaid += (float)$payment->getBaseAmountPaid();
                $posChange += (float)$payment->getAmountChange();
                $basePosChange += (float)$payment->getBaseAmountChange();
                if (!$payment->getIsPaid()) {
                    $takeAmount += (float)$payment->getAmountPaid();
                    $takeBaseAmount += (float)$payment->getBaseAmountPaid();
                }
                $pm->setAmountPaid(
                    round((float)$payment->getAmountPaid() + (float)$pm->getAmountPaid(), 4)
                );
                $pm->setBaseAmountPaid(
                    round((float)$payment->getBaseAmountPaid() + (float)$pm->getBaseAmountPaid(), 4)
                );
            }
            if ($reCalculateTotal) {
                if ($order->getPosBasePreTotalPaid() > 0 && $order->getPosPreTotalPaid() > 0) {
                    if ($oldOrder) {
                        $totalPaid = $oldOrder->getTotalPaid() + $takeAmount;
                        $baseTotalPaid = $oldOrder->getBaseTotalPaid() + $takeBaseAmount;
                    } else {
                        $totalPaid = $order->getTotalPaid() + $takeAmount;
                        $baseTotalPaid = $order->getBaseTotalPaid() + $takeBaseAmount;
                    }
                }
                $order->setTotalPaid(min($totalPaid, $order->getGrandTotal()));
                $order->setBaseTotalPaid(min($baseTotalPaid, $order->getBaseGrandTotal()));
                $totalDue = max($order->getGrandTotal() - $totalPaid, 0);
                $baseTotalDue = max($order->getBaseGrandTotal() - $baseTotalPaid, 0);
                $order->setTotalDue($totalDue);
                $order->setBaseTotalDue($baseTotalDue);
                $order->setPosChange($posChange);
                $order->setBasePosChange($basePosChange);
            }
        }
        $order->setPayment($pm);
        if ($paymentsReferenceNumber) {
            $order->setData('payment_reference_number', $paymentsReferenceNumber);
        }
    }

    /**
     * Add items
     *
     * @param \Magento\Sales\Model\Order $order
     * @param ItemInterface[] $items
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addItemToOrder(&$order, $items)
    {
        $dataItems = [];
        foreach ($items as $item) {
            $id = $item->getItemId();
            $item->setItemId('');
            $item->setOrderId('');
            $item->setQuoteItemId('');

            // check store id
            $storeId = $this->checkStoreId($item->getStoreId());
            $item->setStoreId($storeId);

            $dataItems[$id] = $item->getData();
            $dataItems[$id]['tmp_item_id'] = $id;
            if ($appliedTaxes = $item->getData(ItemInterface::APPLIED_TAXES)) {
                $dataItems[$id]['applied_taxes'] = $appliedTaxes;
            }
        }
        $tmpData = $dataItems;
        $removedData = [];
        foreach ($tmpData as $key => $datum) {
            if (isset($datum['parent_item_id']) && !empty($datum['parent_item_id'])) {
                if ($datum['parent_item_id'] && in_array($datum['parent_item_id'], array_keys($tmpData))) {
                    if (!in_array($datum['parent_item_id'], $this->parentItemId)) {
                        $dataItems[$key]['parent_item'] = $dataItems[$datum['parent_item_id']];
                        $removedData[] = $datum['parent_item_id'];
                    }
                    // check for bundle product
                    $this->parentItemId[$datum['tmp_item_id']] = $datum['parent_item_id'];
                }
            }
        }
        // remove data parent item
        foreach ($removedData as $remove) {
            unset($dataItems[$remove]);
        }

        foreach ($dataItems as $key => $dataItem) {
            /** @var Item $orderItem */
            $orderItem = $this->orderItemFactory->create();

            // check store id
            $storeId = $this->checkStoreId($dataItem['store_id']);
            $dataItem['store_id'] = $storeId;

            // check parent item
            /** @var Item $parentItem */
            $parentItem = $this->orderItemFactory->create();
            if (isset($dataItem['parent_item'])) {
                $parentItemData = $dataItem['parent_item'];
                unset($dataItem['parent_item']);

                // check store id
                $storeId = $this->checkStoreId($parentItemData['store_id']);
                $parentItemData['store_id'] = $storeId;

                $parentItem->setData($parentItemData);
            }

            if (isset($dataItem['parent_item_id'])) {
                unset($dataItem['parent_item_id']);
            }

            $orderItem->setData($dataItem);

            if ($parentItem->getData()) {
                $order->addItem($parentItem);
                $orderItem->setParentItem($parentItem);
            }

            $order->addItem($orderItem);
        }
    }

    /**
     * After create order
     *
     * @param \Magento\Sales\Model\Order $order
     * @param \Magestore\Webpos\Api\Data\Checkout\OrderInterface $params
     * @throws CouldNotSaveException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function afterCreateNewOrder($order, $params)
    {
        if ($order->getShippingAddress()) {
            $order->setData('shipping_address_id', $order->getShippingAddress()->getEntityId());
        }
        if ($order->getBillingAddress()) {
            $order->setData('billing_address_id', $order->getBillingAddress()->getEntityId());
        }
        try {
            $taxIds = [];
            $taxDetail = $params->getAppliedTaxes();
            foreach ($taxDetail as $tax) {
                $taxFactory = $this->detailsFactory->create([]);
                $taxFactory->setAmount($tax['amount']);
                $taxFactory->setCode($tax['code']);
                $taxFactory->setTitle($tax['title']);
                $taxFactory->setOrderId($order->getId());
                $taxFactory->setPercent($tax['percent']);
                $taxFactory->setProcess($tax['process']);
                $taxFactory->setBaseAmount($tax['base_amount']);
                $taxFactory->setBaseRealAmount($tax['base_real_amount']);
                $taxFactory->save();

                $taxIds[$tax['code']] = $taxFactory->getTaxId();
            }

            if (count($taxIds)) {
                /** @var ItemInterface $item */
                foreach ($order->getAllItems() as $item) {
                    if ($appliedTaxes = $item->getAppliedTaxes()) {
                        /** @var TaxInterface $appliedTax */
                        foreach ($appliedTaxes as $code => $appliedTax) {
                            if ($taxId = $taxIds[$code]) {
                                /** @var \Magento\Sales\Model\Order\Tax\Item $orderTaxItem */
                                $orderTaxItem = $this->itemTaxFactory->create();
                                $taxItemData = [
                                    'tax_id' => $taxId,
                                    'item_id' => (int)$item->getItemId(),
                                    'tax_percent' => $appliedTax->getTaxPercent(),
                                    'amount' => $appliedTax->getAmount(),
                                    'base_amount' => $appliedTax->getBaseAmount(),
                                    'real_amount' => $appliedTax->getRealAmount(),
                                    'real_base_amount' => $appliedTax->getRealBaseAmount(),
                                    'associated_item_id' => $appliedTax->getAssociatedItemId(),
                                    'taxable_item_type' => $appliedTax->getTaxableItemType()
                                ];
                                $orderTaxItem->setData($taxItemData)->save();
                            }
                        }
                    }
                }
            }

            $order->setAppliedTaxIsSaved(true);
            $order->save();
            $this->checkOrderItems($order->getAllItems());
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Check order items
     *
     * @param Item[] $items
     * @throws \Exception
     */
    public function checkOrderItems($items)
    {
        foreach ($items as $item) {
            if ($item->getParentItemId() == null && isset($this->parentItemId[$item->getData('tmp_item_id')])) {
                $item->setParentItemId(
                    $this->getItemIdByTmpItemId($this->parentItemId[$item->getData('tmp_item_id')])
                )->save();
            }
        }
    }

    /**
     * Get Item id by tmp_id
     *
     * @param int $tmpItemId
     * @return mixed
     */
    public function getItemIdByTmpItemId($tmpItemId)
    {
        if (!isset($this->itemProductId[$tmpItemId])) {
            /** @var Collection $collection */
            $collection = $this->orderItemFactory->create()->getCollection();
            $collection->addFieldToFilter('tmp_item_id', $tmpItemId);
            $collection->addOrder('item_id', 'DESC');

            $this->itemProductId[$tmpItemId] = $collection->getFirstItem()->getId();
        }
        return $this->itemProductId[$tmpItemId];
    }

    /**
     * Subtract order items qtys from stock items related with order items products.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function subtractOrderInventory($order)
    {
        $items = $this->getProductQty($order->getAllItems());
        $itemsForReindex = $this->stockManagement->registerProductsSale(
            $items,
            $order->getStore()->getWebsiteId()
        );
        $productIds = [];
        foreach ($itemsForReindex as $item) {
            $item->setData('stock_status_changed_auto', 1);
            $item->save();
            $productIds[] = $item->getProductId();
        }
        if (!empty($productIds)) {
            $this->stockIndexerProcessor->reindexList($productIds);
            $this->priceIndexer->reindexList($productIds);
        }

        // update stock status
        $this->resourceStock->updateSetOutOfStock($order->getStore()->getWebsiteId());
        $this->resourceStock->updateSetInStock($order->getStore()->getWebsiteId());
        $this->resourceStock->updateLowStockDate($order->getStore()->getWebsiteId());
    }

    /**
     * Prepare array with information about used product qty and product stock item
     *
     * @param array $relatedItems
     * @return array
     */
    public function getProductQty($relatedItems)
    {
        $items = [];
        foreach ($relatedItems as $item) {
            $productId = $item->getProductId();
            if (!$productId) {
                continue;
            }
            if (isset($items[$productId])) {
                $items[$productId] += $item->getQtyOrdered();
            } else {
                $items[$productId] = $item->getQtyOrdered();
            }
        }
        return $items;
    }

    /**
     * Append Reservations
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function appendReservationsAfterOrderPlacement(OrderInterface $order)
    {
        if ($this->MSIStockManagement->getStockId()) {
            $appendReservations = $this->objectManager
                ->get(AppendReservationsAfterOrderPlacementInterface::class);
            $order = $appendReservations->execute($order);
        }
        return $order;
    }

    /**
     * Create webpos payments
     *
     * @param \Magento\Sales\Model\Order $order
     * @param PaymentInterface[] $payments
     * @throws CouldNotSaveException
     */
    public function createWebposOrderPayment(&$order, $payments)
    {
        $orderPayments = [];
        if (!empty($payments) && count($payments)) {
            foreach ($payments as $payment) {
                if (!$payment->getIsPaid()) {
                    $data = $payment->getData();
                    /** @var Payment $paymentModel */
                    $paymentModel = $this->paymentOrderInterfaceFactory->create();
                    $paymentModel->setData($data);
                    $paymentModel->setOrderId($order->getId());
                    $this->_eventManager->dispatch(
                        'order_webpos_payment_save_before',
                        [
                            'webpos_payment' => $paymentModel,
                            'payment_data' => $payment
                        ]
                    );
                    try {
                        $paymentModel->getResource()->save($paymentModel);
                    } catch (\Exception $exception) {
                        /** @var OrderPaymentError $paymentError */
                        $paymentError = $this->objectManager->create(OrderPaymentError::class);
                        $paymentError->saveErrorLog($paymentModel);
                    }

                    $orderPayments[] = $paymentModel;
                    if ($data['method'] == 'store_credit' && $order->getCustomerId()) {
                        $this->orderRepository->changeCustomerCredit(-$data['base_amount_paid'], $order);
                    }
                }
            }
        }
        if (count($orderPayments)) {
            $order->setPayments($orderPayments);
        }
    }

    /**
     * @inheritdoc
     */
    public function sendEmailOrder(\Magento\Sales\Model\Order $order)
    {
        try {
            $this->emailSender->send($order);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set current store id
     *
     * @param int $storeId
     */
    public function setCurrentStoreId($storeId)
    {
        $this->currentStoreId = $storeId;
    }

    /**
     * Get current store id
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->currentStoreId;
    }

    /**
     * Check store id
     *
     * @param int $storeId
     * @return int
     */
    public function checkStoreId($storeId)
    {
        /** @var Store $store */
        $store = $this->storeFactory->create();
        if (!$store->load($storeId)->getId()) {
            /** @var Store $currentStore */
            $currentStore = $this->storeFactory->create();
            if (!$this->getCurrentStoreId() || !$currentStore->load($this->getCurrentStoreId())->getId()) {
                return $this->helper->getCurrentStoreView()->getId();
            } else {
                return $this->getCurrentStoreId();
            }
        } else {
            return $storeId;
        }
    }

    /**
     * Modify place order params before process
     *
     * @param array $params
     * @return array
     */
    public function modifyPlaceOrderParams($params)
    {
        return $params;
    }
}
