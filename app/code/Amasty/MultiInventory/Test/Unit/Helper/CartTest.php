<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Helper;

use Amasty\MultiInventory\Helper\Cart;
use Amasty\MultiInventory\Model\Warehouse\Shipping;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Directory\Model\PriceCurrency;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Rate\Result;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CartTest
 *
 * @see Cart
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class CartTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Cart|MockObject
     */
    private $cartHelper;

    public function setUp()
    {
        $this->cartHelper = $this->createPartialMock(Cart::class, []);
    }

    /**
     * @covers Cart::calcData
     *
     * @dataProvider calcDataDataProvider
     */
    public function testCalcData($quoteItemData, $expected)
    {
        $quote = $this->createPartialMock(Quote::class, []);

        if ($quoteItemData) {
            $quoteItem = $this->prepareQuoteItem($quoteItemData);
            $quoteItems = [$quoteItem];
        } else {
            $quoteItems = [];
        }

        $result = $this->invokeMethod($this->cartHelper, "calcData", [$quoteItems, $quote]);
        $this->assertEquals($result, $expected);
    }

    public function prepareQuoteItem($quoteItemData)
    {
        $priceCurrency = $this->createPartialMock(PriceCurrency::class, []);
        $quoteItem = $this->createPartialMock(Item::class, []);
        $this->setProperty($quoteItem, 'priceCurrency', $priceCurrency, Item::class);

        $quoteItem->setDiscountPercent($quoteItemData['discount_percent']);
        $quoteItem->setBasePriceInclTax($quoteItemData['base_price_incl_tax']);
        $quoteItem->setWeight($quoteItemData['weight']);
        $quoteItem->setRowWeight($quoteItemData['row_weight']);
        $quoteItem->setData('qty', $quoteItemData['qty']);
        $quoteItem->setData('calculation_price', $quoteItemData['calculation_price']);
        $quoteItem->setData('base_calculation_price', $quoteItemData['base_calculation_price']);

        return $quoteItem;
    }
    /**
     * @covers Cart::sumShipping
     *
     * @dataProvider sumShippingDataProvider
     */
    public function testSumShipping($rates, $expected)
    {
        $sumResults = [];
        foreach ($rates as $rate) {
            $resultMock = $this->initShippingResult($rate['method'], $rate['price']);
            $sumResults[] = $resultMock;
        }

        $result = $this->cartHelper->sumShipping($sumResults)->getAllRates()[0]->getData('price');
        $this->assertEquals(
            $result,
            $expected
        );
    }

    /**
     * @covers Cart::changePrice
     *
     * @dataProvider changePriceDataProvider
     */
    public function testChangePrice($resultShipment, $warehouseShipment, $expectedPrice)
    {
        $shipment = $this->initShippingResult(
            $resultShipment['method'],
            $resultShipment['price'],
            $resultShipment['carrier']
        );
        $warehouse = $this->createPartialMock(
            \Amasty\MultiInventory\Model\Warehouse::class,
            ['getShippings']
        );

        $warehouseShippings = [$this->initWarehouseShipping($warehouseShipment['method'], $warehouseShipment['rate'])];
        $warehouse->expects($this->any())->method('getShippings')->willReturn($warehouseShippings);

        $result = $this->cartHelper->changePrice($shipment, $warehouse)->getAllRates()[0]->getData('price');
        $this->assertEquals(
            $result,
            $expectedPrice
        );
    }

    /**
     * Init ShippingResult mock
     *
     * @param string $method
     * @param string $price
     * @param string $carrier
     *
     * @return MockObject
     * @throws \ReflectionException
     */
    private function initShippingResult($method = '', $price = '', $carrier = '')
    {
        $resultMock = $this->createPartialMock(Result::class, []);
        $priceCurrency = $this->createPartialMock(PriceCurrency::class, []);
        $rateMock = $this->createPartialMock(
            Method::class,
            []
        );
        $this->setProperty($rateMock, 'priceCurrency', $priceCurrency, Method::class);
        $rateMock->setMethod($method);
        $rateMock->setPrice($price);
        $rateMock->setCarrier($carrier);

        $resultMock->append($rateMock);

        return $resultMock;
    }

    /**
     * Init Warehouse\Shipping mock
     *
     * @param $method
     * @param $rate
     *
     * @return MockObject
     */
    private function initWarehouseShipping($method, $rate)
    {
        $warehouseShipping = $this->createPartialMock(
            Shipping::class,
            []
        );
        $warehouseShipping->setShippingMethod($method);
        $warehouseShipping->setRate($rate);

        return $warehouseShipping;
    }

    /**
     * Data provider for sumShipping test
     *
     * @return array
     */
    public function sumShippingDataProvider()
    {
        return [
            [
                [
                    [
                        'method' => 'test1',
                        'price'  => 10
                    ],
                    [
                        'method' => 'test1',
                        'price'  => 5
                    ]
                ],
                15
            ],
            [
                [
                    [
                        'method' => 'test1',
                        'price'  => 10
                    ],
                    [
                        'method' => 'test2',
                        'price'  => 5
                    ]
                ],
                10
            ],
            [
                [
                    [
                        'method' => 'test1',
                        'price'  => 10
                    ]
                ],
                10
            ],

        ];
    }

    /**
     * Data privder for changePrice test
     *
     * @return array
     */
    public function changePriceDataProvider()
    {
        return [
            [
                [
                    'method' => 'test1',
                    'price' => 15,
                    'carrier' => 'test1'
                ],
                [
                    'method' => 'test1',
                    'rate' => 10
                ],
                10
            ],
            [
                [
                    'method' => 'test1',
                    'price' => 15,
                    'carrier' => 'test2'
                ],
                [
                    'method' => 'test2',
                    'rate' => 10
                ],
                10
            ],
            [
                [
                    'method' => 'test1',
                    'price' => 15,
                    'carrier' => 'test1'
                ],
                [
                    'method' => 'test2',
                    'rate' => 10
                ],
                15
            ]
        ];
    }

    /**
     * Data provider for calcData test
     *
     * @return array
     */
    public function calcDataDataProvider()
    {
        return [
            [
                [
                    'base_calculation_price' => 5,
                    'calculation_price' => 5,
                    'discount_percent' => 0.2,
                    'qty' => 2,
                    'base_price_incl_tax' => 5,
                    'weight' => 5,
                    'row_weight' => 3
                ],
                [
                    'base_row_total' => 10,
                    'base_discount_amount' => 8,
                    'qty' => 2,
                    'base_subtotal_incl_tax' => 10,
                    'weight' => 5,
                    'package_weight' => 3
                ]
            ],
            [
                [],
                [
                    'base_row_total' => 0,
                    'base_discount_amount' => 0,
                    'qty' => 0,
                    'base_subtotal_incl_tax' => 0,
                    'weight' => 0,
                    'package_weight' => 0
                ]
            ]
        ];
    }
}
