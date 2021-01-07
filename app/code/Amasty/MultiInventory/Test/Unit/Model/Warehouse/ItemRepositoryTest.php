<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Model\Warehouse;

use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\CollectionFactory;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\Warehouse\Item;
use Amasty\MultiInventory\Model\Warehouse\Item\Api;
use Amasty\MultiInventory\Model\Warehouse\ItemFactory;
use Amasty\MultiInventory\Model\Warehouse\ItemRepository;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ItemRepositoryTest
 *
 * @see ItemRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ItemRepositoryTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const API_ITEM_DATA = [
        'warehouse_id' => 1,
        'product_id' => 2,
        'qty' => 3
    ];

    const SKU = 'test';

    /**
     * @var ItemRepository|MockObject
     */
    private $itemRepository;

    /**
     * @var ItemFactory|MockObject
     */
    private $itemFactory;

    /**
     * @var Item|MockObject
     */
    private $item;

    public function setUp()
    {
        $this->itemRepository = $this->createPartialMock(
            ItemRepository::class,
            ['addStock', 'getStocks']
        );

        $this->item = $this->createPartialMock(Item::class, ['setData']);
        $this->itemFactory = $this->createPartialMock(ItemFactory::class, ['create']);
        $this->itemFactory->expects($this->any())->method('create')->willReturn($this->item);
        $this->setProperty(
            $this->itemRepository,
            'factory',
            $this->itemFactory,
            ItemRepository::class
        );
    }

    /**
     * @covers ItemRepository::addStockSku
     */
    public function testAddStockSku()
    {
        $apiItem = $this->createPartialMock(Api::class, ['getItemData']);
        $apiItem->expects($this->once())->method('getItemData')->willReturn(self::API_ITEM_DATA);

        $this->item->expects($this->once())->method('setData')
            ->with(self::API_ITEM_DATA)
            ->willReturn($this->item);

        $this->itemRepository->expects($this->once())->method('addStock')
            ->with($this->item)
            ->willReturn($this->item);

        $result = $this->itemRepository->addStockSku($apiItem);
        $this->assertEquals($this->item, $result);
    }

    /**
     * @covers ItemRepository::getStocksSku
     *
     * @dataProvider getStocksSkuDataProvider
     */
    public function testGetStocksSku($id, $expected)
    {
        $product = $this->createPartialMock(Product::class, []);
        $product->setId($id);
        $productRepository = $this->createPartialMock(ProductRepository::class, ['get']);
        $productRepository->expects($this->once())->method('get')
            ->with(self::SKU)
            ->willReturn($product);
        $this->setProperty(
            $this->itemRepository,
            'productRepository',
            $productRepository,
            ItemRepository::class
        );
        $this->itemRepository->expects($this->any())->method('getStocks')
            ->with($id)
            ->willReturn($this->item);

        $result = $this->itemRepository->getStocksSku(self::SKU);
        $result === null
            ? $this->assertEquals($expected, $result)
            : $this->assertInstanceOf($expected, $result);
    }

    /**
     * @covers ItemRepository::getProducts
     *
     * @dataProvider getProductsDataProvider
     */
    public function testGetProducts($id, $expected)
    {
        $code = 'test';

        $warehouse = $this->createPartialMock(Warehouse::class, []);
        $warehouse->setId($id);

        $warehouseCollection = $this->createPartialMock(
            Collection::class,
            ['addFieldToFilter', 'getFirstItem']
        );
        $warehouseCollection->expects($this->once())->method('addFieldToFilter')
            ->with('code', $code)
            ->willReturn($warehouseCollection);
        $warehouseCollection->expects($this->once())->method('getFirstItem')
            ->willReturn($warehouse);

        $warehouseCollectionFactory = $this->createPartialMock(CollectionFactory::class, ['create']);
        $warehouseCollectionFactory->expects($this->once())->method('create')
            ->willReturn($warehouseCollection);
        $this->setProperty(
            $this->itemRepository,
            'warehouseCollectionFactory',
            $warehouseCollectionFactory,
            ItemRepository::class
        );

        $stockCollection = $this->createPartialMock(
            \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\Collection::class,
            ['addFieldToFilter', 'getItems']
        );
        $stockCollection->expects($this->any())->method('addFieldToFilter')
            ->with('warehouse_id', $id)
            ->willReturn($stockCollection);
        $stockCollection->expects($this->any())->method('getItems')
            ->willReturn('items');

        $stockCollectionFactory = $this->createPartialMock(
            \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item\CollectionFactory::class,
            ['create']
        );
        $stockCollectionFactory->expects($this->any())->method('create')
            ->willReturn($stockCollection);
        $this->setProperty(
            $this->itemRepository,
            'stockCollection',
            $stockCollectionFactory,
            ItemRepository::class
        );

        $result = $this->itemRepository->getProducts($code);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for getStocksSku test
     *
     * @return array
     */
    public function getStocksSkuDataProvider()
    {
        return [
            [null, null],
            [1, Item::class]
        ];
    }

    /**
     * Data provider for getProducts test
     *
     * @return array
     */
    public function getProductsDataProvider()
    {
        return [
            [null, null],
            [1, 'items']
        ];
    }
}
