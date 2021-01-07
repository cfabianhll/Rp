<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Test\Unit\Helper;

use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\LocationProductIndex;
use Amasty\Storelocator\Test\Unit\Traits;
use Magento\Catalog\Model\Product;

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

    const LOCATION_ID = 1;
    const PRODUCT_ID = 2;
    const STORE_ID = 0;

    /**
     * @covers Data::validateLocation
     *
     * @dataProvider validateLocationDataProvider
     *
     * @param bool $isValid
     * @param bool $expectedResult
     *
     * @throws \ReflectionException
     */
    public function testValidateLocation($isValid, $expectedResult)
    {
        /** @var Location $location */
        $location = $this->createPartialMock(
            Location::class,
            []
        );
        $location->setId(self::LOCATION_ID);

        /** @var Product $product */
        $product = $this->createPartialMock(
            Product::class,
            []
        );
        $product->setId(self::PRODUCT_ID);
        /** @var \Magento\Store\Model\Store $store */
        $store = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            []
        );
        $store->setId(self::STORE_ID);
        $storeManager = $this->createPartialMock(
            \Magento\Store\Model\StoreManager::class,
            ['getStore']
        );
        $storeManager->expects($this->once())->method('getStore')
            ->willReturn($store);
        $helper = $this->createPartialMock(Data::class, []);
        $this->setProperty($helper, 'storeManager', $storeManager, Data::class);

        $locationProduct = $this->createPartialMock(
            LocationProductIndex::class,
            ['validateLocation']
        );
        $locationProduct->expects($this->once())->method('validateLocation')
            ->with(self::LOCATION_ID, self::PRODUCT_ID, self::STORE_ID)
            ->willReturn($isValid);
        $this->setProperty($helper, 'locationProduct', $locationProduct, Data::class);

        $result = $helper->validateLocation($location, $product);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for validateLocation test
     * @return array
     */
    public function validateLocationDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }
}
