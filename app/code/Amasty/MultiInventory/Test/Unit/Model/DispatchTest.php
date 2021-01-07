<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Model;

use Amasty\MultiInventory\Model\Dispatch;
use Amasty\MultiInventory\Model\ResourceModel\Warehouse\Collection;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Model\WarehouseRepository;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Locale\TranslatedLists;
use Magento\Framework\Message\Manager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DispatchTest
 *
 * @see Dispatch
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DispatchTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const WH_ID = 1;

    const DEFAULT_WH = ['default_wh'];

    const CALLABLES = [
        'test1' => [
            'is_active' => true
        ]
    ];

    /**
     * @var Dispatch|MockObject
     */
    private $dispatch;

    public function setUp()
    {
        $this->dispatch = $this->createPartialMock(
            Dispatch::class,
            [
                'sendGoogleRequest',
                'getOrderItem',
                'getDirection',
                'getWarehouseCollection',
                'getGeneral',
                'addDefaultWh',
                'searchByAlgorithm'
            ]
        );
    }

    /**
     * @covers Dispatch::getGoogleRequestResult
     *
     * @dataProvider getGoogleRequestResultDataProvider
     */
    public function testGetGoogleRequestResult($status, $setRows, $expected)
    {
        if ($setRows) {
            $rows = [
                [
                    'elements' => [
                        0 => [
                            'status'   => 'OK',
                            'distance' => [
                                'value' => 500
                            ]
                        ]
                    ]
                ],
                [
                    'elements' => [
                        0 => [
                            'status'   => 'REQUEST_DENIED',
                            'distance' => [
                                'value' => 500
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            $rows = null;
        }
        $response = [
            'status' => $status,
            'rows' => $rows,
            'error_message' => 'test'

        ];
        $url = '';
        $addresses = [
            0 => 'address1',
            1 => 'address2'
        ];
        $this->dispatch->expects($this->once())->method('sendGoogleRequest')
            ->with($url)
            ->willReturn($response);
        $messageManager = $this->createPartialMock(
            Manager::class,
            ['addErrorMessage', 'addNoticeMessage']
        );
        $this->setProperty($this->dispatch, 'messageManager', $messageManager, Dispatch::class);

        $result = $this->dispatch->getGoogleRequestResult($url, $addresses);

        if (isset($result[1])) {
            $result[1] = (int)$result[1];
        }
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Dispatch::getShippingAddress
     *
     * @dataProvider getShippingAddressDataProvider
     */
    public function testGetShippingAddress($direction, $address, $expected)
    {
        if ($direction == Dispatch::DIRECTION_QUOTE) {
            $quote = $this->createPartialMock(Quote::class, ['getShippingAddress']);
            $quote->expects($this->any())->method('getShippingAddress')
                ->willReturn($address);
            $quoteItem = $this->createPartialMock(Item::class, ['getQuote']);
            $quoteItem->expects($this->any())->method('getQuote')
                ->willReturn($quote);
            $this->dispatch->setQuoteItem($quoteItem);
        } else {
            $addressMock = $this->createPartialMock(Address::class, []);
            $addressMock->setAddressType($address);
            $order = $this->createPartialMock(Order::class, []);
            $order->setAddresses([$addressMock]);
            $orderItem = $this->createPartialMock(\Magento\Sales\Model\Order\Item::class, ['getOrder']);
            $orderItem->expects($this->any())->method('getOrder')
                ->willReturn($order);
            $this->dispatch->expects($this->any())->method('getOrderItem')
                ->willReturn($orderItem);
        }
        $this->dispatch->expects($this->once())->method('getDirection')
            ->willReturn($direction);

        $result = $this->dispatch->getShippingAddress();

        if ($expected === 'address_obj') {
            $this->assertEquals($addressMock, $result);
        } else {
            $this->assertEquals($expected, $result);
        }
    }

    /**
     * @covers Dispatch::getWarehouseAddresses
     *
     * @dataProvider getWarehouseAddressesDataProvider
     */
    public function testGetWarehouseAddresses($country, $state, $expects)
    {
        $wh = $this->createPartialMock(Warehouse::class, []);
        $whCollection = $this->createPartialMock(Collection::class, ['getItems']);
        $whCollection->expects($this->once())->method('getItems')
            ->willReturn([$wh]);
        $this->dispatch->expects($this->once())->method('getWarehouseCollection')
            ->willReturn($whCollection);

        $localeLists = $this->createPartialMock(TranslatedLists::class, ['getCountryTranslation']);
        $localeLists->expects($this->any())->method('getCountryTranslation')
            ->with($country)
            ->willReturn($country);
        $this->setProperty($this->dispatch, 'localeLists', $localeLists, Dispatch::class);

        $region = $this->createPartialMock(Region::class, ['loadByCode', 'getName']);
        $region->expects($this->any())->method('loadByCode')
            ->with($state, $country)
            ->willReturn($region);
        $region->expects($this->any())->method('getName')
            ->willReturn($state);
        $regionFactory = $this->createPartialMock(RegionFactory::class, ['create']);
        $regionFactory->expects($this->any())->method('create')
            ->willReturn($region);
        $this->setProperty($this->dispatch, 'regionFactory', $regionFactory, Dispatch::class);

        $wh->setId(self::WH_ID);
        $wh->setCountry($country);
        $wh->setState($state);

        $result = $this->dispatch->getWarehouseAddresses();
        $this->assertEquals($expects, $result);
    }

    /**
     * @covers Dispatch::getWarehouseCoordinates
     *
     * @dataProvider getWarehouseCoordinatesDataProvider
     */
    public function testGetWarehouseCoordinates($lat, $lng, $expected)
    {
        $wh = $this->createPartialMock(Warehouse::class, []);
        $whCollection = $this->createPartialMock(Collection::class, ['getItems']);
        $whCollection->expects($this->once())->method('getItems')
            ->willReturn([$wh]);
        $this->dispatch->expects($this->once())->method('getWarehouseCollection')
            ->willReturn($whCollection);

        $warehouseRepository = $this->createPartialMock(WarehouseRepository::class, ['save']);
        $warehouseRepository->expects($this->any())->method('save')
            ->with($wh)
            ->willReturn($wh);
        $this->setProperty($this->dispatch, 'warehouseRepository', $warehouseRepository, Dispatch::class);

        $wh->setId(self::WH_ID);
        $wh->setLat($lat);
        $wh->setLng($lng);

        $result = $this->dispatch->getWarehouseCoordinates();
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Dispatch::calculateDistance
     */
    public function testCalculateDistance()
    {
        $from = [
            'lat' => 20,
            'lng' => 25
        ];
        $to = [
            'lat' => 10,
            'lng' => 5
        ];

        $result = (int)$this->dispatch->calculateDistance($from, $to);
        $this->assertEquals(2415, $result);
    }

    /**
     * Data provider for getGoogleRequestResult test
     *
     * @return array
     */
    public function getGoogleRequestResultDataProvider()
    {
        return [
            ['REQUEST_DENIED', false, []],
            ['OK', false, []],
            ['OK', true, [500, 40030173]]
        ];
    }

    /**
     * Data provider for getShippingAddress
     *
     * @return array
     */
    public function getShippingAddressDataProvider()
    {
        return [
          [Dispatch::DIRECTION_QUOTE, null, null],
          [Dispatch::DIRECTION_QUOTE, 'test', 'test'],
          [Dispatch::DIRECTION_ORDER, null, null],
          [Dispatch::DIRECTION_ORDER, 'shipping', 'address_obj']
        ];
    }

    /**
     * Data provider for getWarehouseAddresses test
     *
     * @return array
     */
    public function getWarehouseAddressesDataProvider()
    {
        return [
            [null, 'test_state', []],
            [
                'test_country',
                'test_state',
                [
                    1 => [
                        'country' => 'test_country',
                        'state'   => 'test_state',
                        'city'    => null,
                        'address' => null,
                        'zip'     => null
                    ]
                ]
            ]
        ];
    }

    /**
     * Data provider for getWarehouseCoordinates test
     *
     * @return array
     */
    public function getWarehouseCoordinatesDataProvider()
    {
        return [
            [null, null, []],
            [20, 20, [self::WH_ID => ['lat' => 20, 'lng' => 20]]]
        ];
    }
}
