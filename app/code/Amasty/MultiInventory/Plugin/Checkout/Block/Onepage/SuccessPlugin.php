<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Checkout\Block\Onepage;

use Magento\Checkout\Block\Onepage\Success;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Context;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\OrderFactory;

/**
 * Class SuccessPlugin
 */
class SuccessPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var Config
     */
    private $orderConfig;

    private static $ORDERS;

    /**
     * SuccessPlugin constructor.
     *
     * @param Session $checkoutSession
     * @param OrderFactory $orderFactory
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Config $orderConfig
     */
    public function __construct(
        Session $checkoutSession,
        OrderFactory $orderFactory,
        DataObjectFactory $objectFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        Config $orderConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->objectFactory   = $objectFactory;
        $this->orderFactory = $orderFactory;
        $this->httpContext = $httpContext;
        $this->orderConfig = $orderConfig;
    }

    /**
     * @param Success $subject
     */
    public function beforeToHtml(Success $subject)
    {
        $orders = $this->getSeparateOrderIds();
        if (is_array($orders) && count($orders) > 1 && !$subject->hasData('multiple_orders')) {
            $data = [];
            foreach ($orders as $orderId) {
                $order = $this->orderFactory->create()->loadByIncrementId($orderId);
                $data[] = $this->objectFactory->create()
                    ->addData([
                        'is_order_visible' => $this->isVisible($order),
                        'view_order_url' => $subject->getUrl(
                            'sales/order/view/',
                            ['order_id' => $order->getEntityId()]
                        ),
                        'print_url' => $subject->getUrl(
                            'sales/order/print',
                            ['order_id' => $order->getEntityId()]
                        ),
                        'can_print_order' => $this->isVisible($order),
                        'can_view_order'  => $this->canViewOrder($order),
                        'order_id'  => $order->getIncrementId()
                    ]);
            }
            $subject->setData('is_multiple_orders', true);
            $subject->setData('multiple_orders', $data);
        }
    }

    /**
     * @return mixed
     */
    private function getSeparateOrderIds()
    {
        if (self::$ORDERS === null) {
            self::$ORDERS = $this->checkoutSession->getSeparetedOrderIds(true);
        }
        return self::$ORDERS;
    }

    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible($order)
    {
        return !in_array(
            $order->getStatus(),
            $this->orderConfig->getInvisibleOnFrontStatuses()
        );
    }

    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder($order)
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH)
        && $this->isVisible($order);
    }
}
