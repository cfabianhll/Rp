<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Model\Item;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Helper\System;
use Amasty\MultiInventory\Model\Dispatch;
use Amasty\MultiInventory\Model\Warehouse\Item\QuantityValidator;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class QuantityValidatorTest
 *
 * @see QuantityValidator
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class QuantityValidatorTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var QuantityValidator|MockObject
     */
    private $quantityValidator;

    public function setUp()
    {
        $this->quantityValidator = $this->createPartialMock(
            QuantityValidator::class,
            ['checkStockItem', 'checkQuoteItem', 'checkQuoteItemQty']
        );
        $system = $this->createPartialMock(
            System::class,
            ['isMultiEnabled', 'getDispatchOrder']
        );
        $system->expects($this->any())->method('isMultiEnabled')
            ->willReturn(true);
        $system->expects($this->any())->method('getDispatchOrder')
            ->willReturn(['test' => 'test']);
        $this->setProperty($this->quantityValidator, 'system', $system, QuantityValidator::class);
        $dispatch = $this->createPartialMock(Dispatch::class, []);
        $helper = $this->createPartialMock(Data::class, ['getDispatch']);
        $helper->expects($this->any())->method('getDispatch')
            ->willReturn($dispatch);
        $this->setProperty($this->quantityValidator, 'helper', $helper, QuantityValidator::class);
    }

    /**
     * @covers QuantityValidator::validate
     */
    public function testValidate()
    {
        $quoteItemProductId = 1;
        $isStock = true;
        $isQty = true;

        $quote = $this->createPartialMock(Quote::class, []);
        $quote->setId(1);
        $quote->setStoreId(1);

        $store = $this->createPartialMock(\Magento\Store\Model\Store::class, []);
        $store->setWebsiteId(1);
        $store->setId(1);
        $product = $this->initProductMock();

        $quoteItem = $this->createPartialMock(Item::class, ['getProduct', 'getStore', 'getQty']);
        $quoteItem->setProductId($quoteItemProductId);
        $quoteItem->setQuote($quote);
        $quoteItem->expects($this->any())->method('getProduct')
            ->willReturn($product);
        $quoteItem->expects($this->any())->method('getStore')
            ->willReturn($store);
        $quoteItem->expects($this->any())->method('getQty')
            ->willReturn(5);

        $event = $this->createPartialMock(Event::class, []);
        $event->setItem($quoteItem);
        $observer = $this->createPartialMock(Observer::class, []);
        $observer->setEvent($event);

        $stockItem = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            []
        );
        $stockRegistry = $this->createPartialMock(StockRegistry::class, ['getStockItem']);
        $stockRegistry->expects($this->any())->method('getStockItem')
            ->with(1, 1)
            ->willReturn($stockItem);
        $this->setProperty(
            $this->quantityValidator,
            'stockRegistry',
            $stockRegistry,
            QuantityValidator::class
        );

        $this->quantityValidator->expects($this->any())->method('checkStockItem')
            ->with($stockItem, $quoteItem)
            ->willReturn($isStock);
        $this->quantityValidator->expects($this->any())->method('checkQuoteItem')
            ->with($quoteItem)
            ->willReturn($isQty);
        $this->quantityValidator->expects($this->any())->method('checkQuoteItemQty')
            ->with($quoteItem, 5)
            ->willReturn(true);

        $result = $this->quantityValidator->validate($observer);
        $this->assertEquals($result, null);
    }

    /**
     * Get mock of product
     *
     * @return MockObject
     */
    private function initProductMock()
    {
        $product = $this->createPartialMock(Product::class, []);
        $product->setId(1);
        $product->setSku('test_sku');
        $product->setName('test_name');

        return $product;
    }
}
