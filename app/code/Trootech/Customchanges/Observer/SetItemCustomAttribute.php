<?php
namespace Trootech\Customchanges\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\OrderFactory;
use Psr\Log\LoggerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;

class SetItemCustomAttribute implements ObserverInterface
{
    protected $_request;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    public function __construct(
        RequestInterface $request,
        LoggerInterface $logger,
        HistoryFactory $historyFactory,
        OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_historyFactory = $historyFactory;
        $this->_orderFactory = $orderFactory;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        $writer = new Stream(BP . '/var/log/deliver_type.log');
        $logger = new Logger();
        $logger->addWriter($writer);
        /* Code here */
        $quote_item = $observer->getEvent()->getQuoteItem();
        $quote = $quote_item->getQuote();
        $shippingType = $this->_request->getPost('shipping_type');
        $pickupCode =  $this->_request->getPost('pickup_store_id');
        if (!isset($shippingType)) {
            $shippingType = 'delivery';
        }

        $quote_item->setShippingType($shippingType);
        $quote_item->setPickupStoreId($pickupCode);

        $quote->setData('shipping_type', $shippingType);
        $quote->setData('pickup_store_id', $pickupCode);

        $quote_item->getProduct()->setIsSuperMode(true);
        $logger->info("success !!!!");
    }
}