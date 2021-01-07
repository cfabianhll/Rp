<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Helper;

use Amasty\MultiInventory\Helper\Distance;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Framework\Message\Manager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DistanceTest
 *
 * @see Distance
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DistanceTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Distance|MockObject
     */
    private $distanceHelper;

    public function setUp()
    {
        $this->distanceHelper = $this->createPartialMock(Distance::class, []);
    }

    /**
     * @covers Distance::prepareAddressForGoogle
     *
     * @dataProvider prepareAddressForGoogleDataProvider
     */
    public function testPrepareAddressForGoogle($country, $region, $zip, $expected)
    {
        $data = [
            'country' => $country,
            'region' => [
                'region' => $region
            ],
            'zip' => $zip
        ];

        $result = $this->distanceHelper->prepareAddressForGoogle($data);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Distance::checkResponse
     *
     * @dataProvider checkResponseDataProvider
     */
    public function testCheckResponse($status, $location, $expected)
    {
        $messageManager = $this->createPartialMock(
            Manager::class,
            ['addErrorMessage', 'addNoticeMessage']
        );
        $this->setProperty($this->distanceHelper, 'messageManager', $messageManager, Distance::class);

        if ($status) {
            $response = [
                'status' => $status,
                'results' => [
                    0 => [
                        'geometry' => [
                            'location' => $location
                        ]
                    ]
                ]
            ];
        } else {
            $response = [];
        }

        $result = $this->invokeMethod($this->distanceHelper, 'checkResponse', [$response]);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for prepareAddressForGoogle
     *
     * @return array
     */
    public function prepareAddressForGoogleDataProvider()
    {
        return [
            ['a', 'b', 'c', 'a+b+c'],
            ['a', '', 'c', 'a+c'],
            ['a', '', '', 'a'],
            ['', '', '', '']
        ];
    }

    /**
     * Data provider for checkResponse test
     *
     * @return array
     */
    public function checkResponseDataProvider()
    {
        return [
            ['OK', null, false],
            ['OK', 'test', 'test'],
            [null, null, false],
            ['INVALID_REQUEST', null, false]
        ];
    }
}