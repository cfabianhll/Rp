<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Model\Export;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\Data\StocktakingItemInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\SourceProduct\Collection as SourceProductCollection;
use Magestore\Stocktaking\Model\Export\CsvExportHandler;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\StocktakingItem\Collection as ItemCollection;

/**
 * Class CsvExportHandlerTest
 *
 * Used for test Csv Export Handler
 * @magentoAppArea adminhtml
 */
class CsvExportHandlerTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SourceProductCollection
     */
    protected $sourceProductCollection;

    /**
     * @var CsvExportHandler
     */
    protected $csvExportHandler;

    /**
     * Setup environment
     *
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->getRequest()->setParams(['source_code' => 'source-code-1']);
        $this->sourceProductCollection = $this->objectManager->create(SourceProductCollection::class);
        $this->csvExportHandler = $this->objectManager->get(CsvExportHandler::class);
    }

    /**
     * Test Get Uncounted Csv Data
     *
     * @param array $expectedResult
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/source.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/source_items_on_source_1.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_full_with_products.php
     * @dataProvider getUncountedCsvDataTestDataProvider
     */
    public function testGetUncountedCsvData(array $expectedResult)
    {
        $stocktaking = $this->getStocktaking();
        $result = $this->csvExportHandler->getUncountedCsvData(
            $this->sourceProductCollection->getUncountedSkuStocktaking($stocktaking->getId())
        );
        $this->assertEquals(
            $expectedResult,
            $result,
            'Uncounted Csv data is wrong'
        );
    }

    /**
     * Test Get Item Different Csv Data Partial Stocktaking
     *
     * @param array $expectedResult
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryCatalog::Test/_files/source_items_on_default_source.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_partial_with_products.php
     * @dataProvider getGetItemDifferentPartialStocktakingDataProvider
     */
    public function testGetItemDifferentCsvDataPartialStocktaking(array $expectedResult)
    {
        /** @var ItemCollection $stocktakingItemCollection */
        $stocktakingItemCollection = $this->objectManager->create(ItemCollection::class);

        $result = $this->csvExportHandler->getItemDifferentCsvData(
            $stocktakingItemCollection->addFieldToFilter(
                StocktakingItemInterface::STOCKTAKING_ID,
                $this->getPartialStocktaking()->getId()
            )->getDifferentCountedCollection(),
            null
        );
        $this->assertEquals(
            $expectedResult,
            $result
        );
    }

    /**
     * Test Get Item Different Csv Data Partial Stocktaking
     *
     * @param array $expectedResult
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/source.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/source_items_on_source_1.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_full_with_products.php
     * @dataProvider getGetItemDifferentFullStocktakingDataProvider
     */
    public function testGetItemDifferentCsvDataFullStocktaking(array $expectedResult)
    {
        /** @var SourceProductCollection $sourceProductCollection */
        $sourceProductCollection = $this->objectManager->create(SourceProductCollection::class);
        $uncountedSku = $sourceProductCollection->getDifferentNotInStocktaking(
            $this->getStocktaking()->getId()
        );
        /** @var ItemCollection $stocktakingItemCollection */
        $stocktakingItemCollection = $this->objectManager->create(ItemCollection::class);

        $result = $this->csvExportHandler->getItemDifferentCsvData(
            $stocktakingItemCollection->addFieldToFilter(
                StocktakingItemInterface::STOCKTAKING_ID,
                $this->getStocktaking()->getId()
            )->getDifferentCountedCollection(),
            $uncountedSku
        );
        $this->assertEquals(
            $expectedResult,
            $result
        );
    }

    /**
     * Request getter
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        if (!$this->request) {
            $this->request = $this->objectManager->get(RequestInterface::class);
        }
        return $this->request;
    }

    /**
     * Get stocktaking
     *
     * @return StocktakingInterface|null
     */
    public function getStocktaking()
    {
        return $this->objectManager->create(Collection::class)
            ->addFieldToFilter(StocktakingInterface::CODE, 'ST00000002')
            ->getFirstItem();
    }

    /**
     * Get partial stocktaking
     *
     * @return StocktakingInterface|null
     */
    public function getPartialStocktaking()
    {
        return $this->objectManager->create(Collection::class)
            ->addFieldToFilter(StocktakingInterface::CODE, 'ST00000001')
            ->getFirstItem();
    }

    /**
     * Get Uncounted Csv Data Test Data Provider
     *
     * @return array
     */
    public function getUncountedCsvDataTestDataProvider()
    {
        return [
            'Uncounted products on source 1' => [
                'expectedResult' => [
                    [__('SKU'), __('COUNTED QUANTITY'), __('REASON OF DIFFERENCE')],
                    ['SKU-4', '', ''],
                    ['SKU-5', '', ''],
                    ['SKU-6', '', '']
                ]
            ]
        ];
    }

    /**
     * Get Different List In Partial Stocktaking Test Data Provider
     *
     * @return array
     */
    public function getGetItemDifferentPartialStocktakingDataProvider()
    {
        return [
            'Base case' => [
                'expectedResult' => [
                    [__('SKU'), __('CURRENT QUANTITY'), __('COUNTED QUANTITY'), __('REASON OF DIFFERENCE')],
                    ['SKU-1', 5.5, 10.5, 'Difference reason 1'],
                    ['SKU-3', 6, 0, '']
                ]
            ]
        ];
    }

    /**
     * Get Different List In Full Stocktaking Test Data Provider
     *
     * @return array
     */
    public function getGetItemDifferentFullStocktakingDataProvider()
    {
        return [
            'Base case' => [
                'expectedResult' => [
                    [__('SKU'), __('CURRENT QUANTITY'), __('COUNTED QUANTITY'), __('REASON OF DIFFERENCE')],
                    ['SKU-1', 5.5, 10.5, 'Difference reason 1'],
                    ['SKU-3', 6, 0, ''],
                    ['SKU-5', 2, 0, '']
                ]
            ]
        ];
    }
}
