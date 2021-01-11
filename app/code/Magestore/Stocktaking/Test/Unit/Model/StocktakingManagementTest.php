<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types = 1);

namespace Magestore\Stocktaking\Test\Unit\Model;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryImportExport\Model\Import\SourceItemConvert;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\Collection as SourceProductCollection;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\CollectionFactory
    as SourceProductCollectionFactory;
use Magestore\Stocktaking\Model\StocktakingManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class \Magestore\Stocktaking\Model\StocktakingManagement
 */
class StocktakingManagementTest extends TestCase
{
    /**
     * @var StocktakingManagement
     */
    protected $stockManagement;
    /**
     * @var SourceProductCollectionFactory|MockObject
     */
    protected $sourceProductCollectionFactoryMock;
    /**
     * @var SourceProductCollection|MockObject
     */
    protected $sourceProductCollectionMock;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var StocktakingItemRepositoryInterface|MockObject
     */
    protected $stocktakingItemRepositoryMock;
    /**
     * @var EventManagerInterface
     */
    protected $eventManagerMock;
    /**
     * @var SourceItemConvert|MockObject
     */
    protected $sourceItemConvertMock;
    /**
     * @var SourceItemsSaveInterface|MockObject
     */
    protected $sourceItemsSaveMock;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $testObjectManager = new ObjectManager($this);
        $this->sourceProductCollectionFactoryMock = $this->getMockBuilder(SourceProductCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceProductCollectionMock = $this->getMockBuilder(SourceProductCollection::class)
            ->setMethods(['addFieldToFilter', 'isLoaded', 'load', 'toArray', 'getUncountedSkuStocktaking'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sourceProductCollectionFactoryMock->expects($this->any())
            ->method('create')->willReturn($this->sourceProductCollectionMock);

        $this->serializer = $testObjectManager->getObject(Json::class);

        $this->stocktakingItemRepositoryMock = $this->getMockBuilder(StocktakingItemRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addStocktakingItems', 'getListByStocktakingId'])
            ->getMockForAbstractClass();

        $this->eventManagerMock = $this->getMockBuilder(EventManagerInterface::class)
            ->setMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sourceItemConvertMock = $this->getMockBuilder(SourceItemConvert::class)
            ->setMethods(['convert'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->sourceItemsSaveMock = $this->getMockBuilder(SourceItemsSaveInterface::class)
            ->setMethods(['execute'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockManagement = $testObjectManager->getObject(
            StocktakingManagement::class,
            [
                'sourceProductCollectionFactory' => $this->sourceProductCollectionFactoryMock,
                'serializer' => $this->serializer,
                'stocktakingItemRepository' => $this->stocktakingItemRepositoryMock,
                'eventManager' => $this->eventManagerMock,
                'sourceItemConvert' => $this->sourceItemConvertMock,
                'sourceItemsSave' => $this->sourceItemsSaveMock
            ]
        );
    }

    /**
     * Test function getSelectBarcodeProductListJson
     *
     * @param array $collectionItems
     * @param string $expectedResult
     * @param array $params
     * @covers StocktakingManagement::__construct
     * @covers StocktakingManagement::getSelectBarcodeProductListJson
     * @dataProvider providerGetSelectBarcodeProductListJson
     */
    public function testGetSelectBarcodeProductListJson(
        array $collectionItems,
        string $expectedResult,
        array $params
    ) {
        $this->sourceProductCollectionMock->expects($this->once())
            ->method('addFieldToFilter')->willReturnSelf();
        $this->sourceProductCollectionMock->expects($this->once())
            ->method('isLoaded')->willReturn(false);
        $this->sourceProductCollectionMock->expects($this->once())
            ->method('load')->willReturnSelf();
        $this->sourceProductCollectionMock->expects($this->once())
            ->method('toArray')->willReturn($collectionItems);

        $this->sourceProductCollectionFactoryMock->expects($this->once())
            ->method('create')->willReturn($this->sourceProductCollectionMock);
        $this->assertEquals(
            $expectedResult,
            $this->stockManagement->getSelectBarcodeProductListJson($params['productIds'])
        );
    }

    /**
     * Test function getSelectBarcodeProductListJson without params
     *
     * @param array $collectionItems
     * @param string $expectedResult
     * @covers StocktakingManagement::__construct
     * @covers StocktakingManagement::getSelectBarcodeProductListJson
     * @dataProvider providerGetSelectBarcodeProductListJson
     */
    public function testGetSelectBarcodeProductListJsonWithoutParams(
        array $collectionItems,
        string $expectedResult
    ) {
        $this->sourceProductCollectionMock->expects($this->once())
            ->method('isLoaded')->willReturn(true);
        $this->sourceProductCollectionMock->expects($this->once())
            ->method('toArray')->willReturn($collectionItems);

        $this->sourceProductCollectionFactoryMock->expects($this->once())
            ->method('create')->willReturn($this->sourceProductCollectionMock);
        $this->assertEquals(
            $expectedResult,
            $this->stockManagement->getSelectBarcodeProductListJson()
        );
    }

    /**
     * @return array
     */
    public function providerGetSelectBarcodeProductListJson()
    {
        return [
            'base case' => [
                'collectionItems' => [
                    [
                        'quantity' => 15,
                        'barcode' => '24-MB01,24-MB02'
                    ],
                    [
                        'barcode' => '24-UG01,24-UG02'
                    ],
                    [
                        'quantity' => 20
                    ]
                ],
                'expectedResult' => '{"24-MB01":{"quantity":15,"barcode":"24-MB01,24-MB02"},'
                    . '"24-MB02":{"quantity":15,"barcode":"24-MB01,24-MB02"},'
                    . '"24-UG01":{"barcode":"24-UG01,24-UG02"},"24-UG02":{"barcode":"24-UG01,24-UG02"}}',
                'params' => [
                    'productIds' => [1]
                ],
            ]
        ];
    }

    /**
     * Test function addUncountedProductToStocktaking
     *
     * @covers StocktakingManagement::__construct
     * @covers StocktakingManagement::addUncountedProductToStocktaking
     */
    public function testAddUncountedProductToStocktaking()
    {
        $params = [
            'stocktakingId' => 1
        ];

        // Model which will be NOT added to stock taking
        $productMockModel1 = $this->getMockBuilder(ProductModel::class)
            ->setMethods(['getQtyInSource', 'getId', 'getName', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMockModel1->expects($this->once())->method('getQtyInSource')->willReturn(0);
        $productMockModel1->expects($this->never())->method('getId');
        $productMockModel1->expects($this->never())->method('getName');
        $productMockModel1->expects($this->never())->method('getSku');

        // Model which will be added to stock taking
        $productMockModel2 = $this->getMockBuilder(ProductModel::class)
            ->setMethods(['getQtyInSource', 'getId', 'getName', 'getSku'])
            ->disableOriginalConstructor()
            ->getMock();
        $productMockModel2->expects($this->exactly(2))->method('getQtyInSource')->willReturn(1);
        $productMockModel2->expects($this->once())->method('getId');
        $productMockModel2->expects($this->once())->method('getName');
        $productMockModel2->expects($this->once())->method('getSku');

        $this->sourceProductCollectionMock->expects($this->once())
            ->method('getUncountedSkuStocktaking')->willReturn([$productMockModel1, $productMockModel2]);

        $this->sourceProductCollectionFactoryMock->expects($this->once())
            ->method('create')->willReturn($this->sourceProductCollectionMock);

        $this->stocktakingItemRepositoryMock->expects($this->once())
            ->method('addStocktakingItems');
        $this->stockManagement->addUncountedProductToStocktaking($params['stocktakingId']);
    }
}
