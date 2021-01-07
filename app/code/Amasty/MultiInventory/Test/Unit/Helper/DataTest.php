<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Helper;

use Amasty\MultiInventory\Helper\Data;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\Collection;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Quote\Item\CollectionFactory;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DataTest
 *
 * @see Data
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const QUOTE_ID = 1;

    const WH_ID = 2;

    const PRODUCT_ID = 3;

    const QTY = 5;

    const QTY_ORDERED = 8;

    /**
     * @var Data|MockObject
     */
    private $dataHelper;

    public function setUp()
    {
        $this->dataHelper = $this->createPartialMock(Data::class, ['isSimple']);
    }

    /**
     * @covers Data::dispatchWarehouseForQuote
     *
     * @dataProvider dispatchWarehouseForQuoteDataProvider
     */
    public function testDispatchWarehouseForQuote($parentItem, $isSimple, $expected)
    {
        $orderMock = $this->prepareOrderMock($parentItem, $isSimple);
        $whCollection = $this->createPartialMock(
            Collection::class,
            ['getWarehousesFromQuote', 'getItemByColumnValue']
        );
        $warehouseQuote = $this->createPartialMock(Item::class, ['getWarehouseId']);
        $warehouseQuote->expects($this->any())->method('getWarehouseId')
            ->willReturn(self::WH_ID);
        $whCollection->expects($this->once())->method('getWarehousesFromQuote')
            ->willReturn($whCollection);
        $whCollection->expects($this->any())->method('getItemByColumnValue')
            ->with('quote_item_id', self::QUOTE_ID)
            ->willReturn($warehouseQuote);
        $whCollectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $whCollectionFactory->expects($this->once())->method('create')
            ->willReturn($whCollection);
        $this->setProperty($this->dataHelper, 'quoteItemWhCollection', $whCollectionFactory, Data::class);

        $result = $this->dataHelper->dispatchWarehouseForQuote($orderMock);
        $this->assertEquals($result, $expected);
    }

    /**
     * Prepare order mock
     */
    private function prepareOrderMock($parentItem, $isSimple)
    {
        $orderMock = $this->createPartialMock(
            Order::class,
            ['getAllItems']
        );
        $orderMock->setQuoteId(self::QUOTE_ID);

        $orderItem = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getParentItemId']
        );
        $orderItem->setQuoteItemId(self::QUOTE_ID);
        $orderItem->setOrderId(2);
        $orderItem->setId(2);
        $orderItem->setProductId(2);
        $orderItem->setQtyOrdered(5);
        $orderItem->expects($this->once())->method('getParentItemId')
            ->willReturn($parentItem);
        $this->dataHelper->expects($this->any())->method('isSimple')
            ->willReturn($isSimple);
        $orderMock->expects($this->once())->method('getAllItems')
            ->willReturn([$orderItem]);

        return $orderMock;
    }

    /**
     * @covers Data::checkForBundle
     *
     * @dataProvider checkForBundleDataProvider
     */
    public function testCheckForBundle($parentItem, $orderItemId, $typeId, $expected)
    {
        $fields = [
            'parent_item_id' => $parentItem
        ];
        $item = $this->createPartialMock(DataObject::class, []);
        $item->setData('order_item_id', $orderItemId);
        $item->setProductId(self::PRODUCT_ID);
        $items = [
            $item
        ];

        $entity = $this->createPartialMock(DataObject::class, []);
        $entity->setData('items', $items);

        $product = $this->createPartialMock(Product::class, []);
        $product->setTypeId($typeId);
        $productRepository = $this->createPartialMock(ProductRepository::class, ['getById']);
        $productRepository->expects($this->any())->method('getById')
            ->with(self::PRODUCT_ID)
            ->willReturn($product);
        $this->setProperty(
            $this->dataHelper,
            'productRepository',
            $productRepository,
            Data::class
        );

        $result = $this->dataHelper->checkForBundle($fields, $entity);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Data::getQtyToShip
     *
     * @dataProvider getQtyToShipDataProvider
     */
    public function testGetQtyToShip($parentItem, $orderItemId, $expected)
    {
        $field = [
            'parent_item_id' => $parentItem,
            'qty_ordered' => self::QTY_ORDERED
        ];
        $item = $this->createPartialMock(DataObject::class, []);
        $item->setData('order_item_id', $orderItemId);
        $item->setQty(self::QTY);
        $items = [
            $item
        ];

        $entity = $this->createPartialMock(DataObject::class, []);
        $entity->setData('items', $items);

        $result = $this->invokeMethod($this->dataHelper, 'getQtyToShip', [$entity, $field]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for dispatchWarehouseForQuote test
     *
     * @return array
     */
    public function dispatchWarehouseForQuoteDataProvider()
    {
        return [
            [
                true,
                true,
                [
                    0 => [
                        'order_id' => 2,
                        'order_item_id' => 2,
                        'product_id' => 2,
                        'qty' => 5,
                        'warehouse_id' => self::WH_ID
                    ]
                ]
            ],
            [
                true,
                false,
                [
                    0 => [
                        'order_id' => 2,
                        'order_item_id' => 2,
                        'product_id' => 2,
                        'qty' => 5,
                        'warehouse_id' => self::WH_ID
                    ]
                ]
            ],
            [
                false,
                false,
                []
            ],
            [
                false,
                true,
                [
                    0 => [
                        'order_id' => 2,
                        'order_item_id' => 2,
                        'product_id' => 2,
                        'qty' => 5,
                        'warehouse_id' => self::WH_ID
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider for checkForBundle test
     *
     * @return array
     */
    public function checkForBundleDataProvider()
    {
        return [
            [1, 1, 'bundle', true],
            [1, 2, 'bundle', false],
            [1, 1, 'simple', false],
            [null, 1, 'simple', false]
        ];
    }

    /**
     * Data provider for getQtyToShip test
     *
     * @return array
     */
    public function getQtyToShipDataProvider()
    {
        return [
            [1, 1, self::QTY],
            [1, 2, self::QTY_ORDERED]
        ];
    }
}