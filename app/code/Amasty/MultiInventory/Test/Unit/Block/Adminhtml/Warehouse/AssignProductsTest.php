<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Test\Unit\Block\Adminhtml\Warehouse;

use Amasty\MultiInventory\Block\Adminhtml\Warehouse\AssignProducts;
use Amasty\MultiInventory\Block\Adminhtml\Warehouse\Tab\Product;
use Amasty\MultiInventory\Model\Warehouse;
use Amasty\MultiInventory\Test\Unit\Traits;
use Magento\Framework\Json\Encoder;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AssignProductsTest
 *
 * @see AssignProducts
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AssignProductsTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    const BLOCK_GRID_SUCCESS = 'created';

    const PRODUCTS_ARRAY = [1, 2];

    /**
     * @var AssignProducts|MockObject
     */
    private $assign;

    public function setUp()
    {
        $this->assign = $this->createPartialMock(
            AssignProducts::class,
            ['getLayout', 'getWarehouse']
        );
        $jsonEncoder = $this->createPartialMock(Encoder::class, ['encode']);
        $jsonEncoder->expects($this->any())->method('encode')
            ->with(self::PRODUCTS_ARRAY)
            ->willReturn('encoded');
        $this->setProperty(
            $this->assign,
            'jsonEncoder',
            $jsonEncoder,
            AssignProducts::class
        );
    }

    /**
     * @covers AssignProducts::getBlockGrid
     *
     * @dataProvider getBlockGridDataProvider
     */
    public function testGetBlockGrid($created)
    {
        if ($created) {
            $this->setProperty(
                $this->assign,
                'blockGrid',
                self::BLOCK_GRID_SUCCESS,
                AssignProducts::class
            );
        }
        $layout = $this->createPartialMock(\Magento\Framework\View\Layout::class, ['createBlock']);
        $layout->expects($this->any())->method('createBlock')
            ->with(Product::class, 'warehouse.product.grid')
            ->willReturn(self::BLOCK_GRID_SUCCESS);
        $this->assign->expects($this->any())->method('getLayout')
            ->willReturn($layout);

        $result = $this->assign->getBlockGrid();
        $this->assertEquals(self::BLOCK_GRID_SUCCESS, $result);

        $result = $this->assign->getBlockGrid();
        $this->assertEquals(self::BLOCK_GRID_SUCCESS, $result);
    }

    /**
     * @covers AssignProducts::getProductsJson
     *
     * @dataProvider getProductsJsonDataProvider
     */
    public function testGetProductsJson($products, $expected)
    {
        $warehouse = $this->createPartialMock(Warehouse::class, ['getProductsToGrid']);
        $warehouse->expects($this->once())->method('getProductsToGrid')
            ->willReturn($products);
        $this->assign->expects($this->once())->method('getWarehouse')
            ->willReturn($warehouse);

        $result = $this->assign->getProductsJson();
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for getBlockGrid test
     *
     * @return array
     */
    public function getBlockGridDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    /**
     * Data provider for getProductsJson test
     *
     * @return array
     */
    public function getProductsJsonDataProvider()
    {
        return [
            [null, '{}'],
            [self::PRODUCTS_ARRAY, 'encoded']
        ];
    }
}
