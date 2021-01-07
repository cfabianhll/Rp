<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Model\Warehouse\Order;

use Amasty\MultiInventory\Model\Warehouse\Order\Processor;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Directory\Model\PriceCurrency;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\ItemRepository;
use Magento\Sales\Model\OrderRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProcessorTest
 *
 * @see Processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ProcessorTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const ORDER_ITEM_ID = 5;

    /**
     * @var Processor|MockObject
     */
    private $processor;

    /**
     * @var ItemRepository|MockObject
     */
    private $orderItemRepository;

    public function setUp()
    {
        $this->processor = $this->createPartialMock(
            Processor::class,
            []
        );
        $priceCurrency = $this->createPartialMock(
            PriceCurrency::class,
            ['convert']
        );
        $priceCurrency->expects($this->any())->method('convert')
            ->with(1.0)
            ->willReturn(1);
        $this->setProperty(
            $this->processor,
            'priceCurrency',
            $priceCurrency,
            Processor::class
        );
        $this->orderItemRepository = $this->createPartialMock(ItemRepository::class, ['get']);
        $this->setProperty(
            $this->processor,
            'orderItemRepository',
            $this->orderItemRepository,
            Processor::class
        );
        $orderRepository = $this->createPartialMock(OrderRepository::class, ['save']);
        $this->setProperty(
            $this->processor,
            'orderRepository',
            $orderRepository,
            Processor::class
        );
    }

    /**
     * @covers Processor::changeDataOrder
     */
    public function testChangeDataOrder()
    {
        $result = [
            1 => [
                'order_item_id' => self::ORDER_ITEM_ID
            ]
        ];
        $items = [
            1
        ];
        $order = $this->createPartialMock(\Magento\Sales\Model\Order::class, []);
        $baseShippingAmount = 1;

        $orderItem = $this->createOrderItem();
        $this->orderItemRepository->expects($this->once())->method('get')
            ->with(self::ORDER_ITEM_ID)
            ->willReturn($orderItem);

        $resultFun = $this->invokeMethod(
            $this->processor,
            'changeDataOrder',
            [$result, $items, $order, $baseShippingAmount]
        );
        $this->assertEquals(30.0, $resultFun->getSubtotal());
    }

    /**
     * Get mock of orderItem
     *
     * @return MockObject
     */
    private function createOrderItem()
    {
        $orderItem = $this->createPartialMock(Item::class, []);
        $orderItem->setQtyOrdered(2);
        $orderItem->setPrice(15);
        $orderItem->setBasePrice(20);
        $orderItem->setPriceInclTax(18);
        $orderItem->setBasePriceInclTax(22);
        $orderItem->setDiscountPercent(0.2);
        $orderItem->setTaxPercent(0.1);

        return $orderItem;
    }
}
