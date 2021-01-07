<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Model;

use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\Warehouse\ItemRepository;
use Amasty\MultiInventory\Model\Warehouse\Order\Processor;
use Amasty\MultiInventory\Observer\CheckoutAllSubmitAfterObserver;
use Amasty\MultiInventory\Test\Unit\Traits;

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CheckoutAllSubmitAfterObserverTest
 *
 * @see CheckoutAllSubmitAfterObserver
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CheckoutAllSubmitAfterObserverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const PRODUCT_ID = 1;

    const WAREHOUSE_ID = 1;

    /**
     * @var CheckoutAllSubmitAfterObserver|MockObject
     */
    private $observer;

    /**
     * @var Item|MockObject
     */
    private $warehouseItem;

    public function setUp()
    {
        $this->observer = $this->createPartialMock(
            CheckoutAllSubmitAfterObserver::class,
            []
        );
        $this->warehouseItem = $this->createPartialMock(Item::class, ['recalcAvailable']);
        $itemRepository = $this->createPartialMock(
            ItemRepository::class,
            ['getByProductWarehouse', 'save']
        );
        $itemRepository->expects($this->once())->method('getByProductWarehouse')
            ->with(self::PRODUCT_ID, self::WAREHOUSE_ID)
            ->willReturn($this->warehouseItem);
        $itemRepository->expects($this->once())->method('save')
            ->with($this->warehouseItem);
        $this->setProperty(
            $this->observer,
            'itemRepository',
            $itemRepository,
            CheckoutAllSubmitAfterObserver::class
        );

        $system = $this->createPartialMock(System::class, ['getPhysicalDecreese']);
        $system->expects($this->once())->method('getPhysicalDecreese')
            ->willReturn(1);
        $this->setProperty(
            $this->observer,
            'system',
            $system,
            CheckoutAllSubmitAfterObserver::class
        );

        $processor = $this->createPartialMock(Processor::class, ['reindexRow']);
        $processor->expects($this->once())->method('reindexRow')
            ->with(self::PRODUCT_ID);
        $this->setProperty(
            $this->observer,
            'processor',
            $processor,
            CheckoutAllSubmitAfterObserver::class
        );
    }

    /**
     * @covers CheckoutAllSubmitAfterObserver::modifyWarehouseStock
     */
    public function testModifyWarehouseStock()
    {
        $itemWrapper = [
            'product_id' => self::PRODUCT_ID,
            'warehouse_id' => self::WAREHOUSE_ID,
            'qty' => 2
        ];

        $result = $this->invokeMethod($this->observer, 'modifyWarehouseStock', [$itemWrapper]);
        $this->assertEquals($this->warehouseItem, $result);
    }
}
