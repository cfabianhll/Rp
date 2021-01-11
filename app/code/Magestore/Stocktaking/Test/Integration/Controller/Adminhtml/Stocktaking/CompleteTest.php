<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Controller\Adminhtml\Stocktaking;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magestore\AdjustStock\Api\Data\AdjustStock\AdjustStockInterface;
use Magestore\AdjustStock\Model\ResourceModel\AdjustStock\Collection as AdjustCollection;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\Collection as ArchiveCollection;

/**
 * Class CompleteTest
 *
 * Used for test complete and adjust stocktaking
 * @magentoAppArea adminhtml
 */
class CompleteTest extends AbstractBackendController
{
    /**
     * Url to save
     *
     * @inheritDoc
     */
    protected $uri = 'backend/stocktaking/stocktaking/complete';

    /**
     * @var string
     */
    protected $resource = 'Magestore_Stocktaking::complete';

    /**
     * Test complete and adjust stock
     *
     * @param int $type
     * @param array $expectedResult
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/source.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/source_items_on_source_1.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_with_product_different.php
     * @dataProvider stocktakingTypeDataProvider
     */
    public function testCompleteAndAdjust(int $type, array $expectedResult)
    {
        $this->prepareRequestToComplete($type);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'The stock-taking has been completed.'
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );

        // stocktaking move to archive and status completed
        $this->assertEquals(
            StocktakingArchiveInterface::STATUS_COMPLETED,
            $this->getStocktakingArchive($type)->getStatus()
        );

        // stocktaking removed from stocktaking grid
        $this->assertEquals(
            null,
            $this->getStocktaking($type)->getId()
        );

        // Assert adjust stock
        $adjustStock = $this->getAdjustStock();

        $this->assertEquals(
            $expectedResult['source_code'],
            $adjustStock->getSourceCode()
        );

        $this->assertEquals(
            $expectedResult['source_name'],
            $adjustStock->getSourceName()
        );

        $this->assertEquals(
            'Adjust from stock-taking ' . $expectedResult['code'],
            $adjustStock->getReason()
        );

        // Assert adjust stock Product list
        $productList = $adjustStock->getProductCollection();

        $this->assertEquals(
            $expectedResult['product_sku'],
            $productList->getColumnValues('product_sku')
        );

        $this->assertEquals(
            $expectedResult['old_qty'],
            $productList->getColumnValues('old_qty')
        );

        $this->assertEquals(
            $expectedResult['new_qty'],
            $productList->getColumnValues('new_qty')
        );

        $this->assertEquals(
            $expectedResult['change_qty'],
            $productList->getColumnValues('change_qty')
        );
    }

    /**
     * Prepare request
     *
     * @param int $type
     */
    public function prepareRequestToComplete(int $type)
    {
        $stocktaking = $this->getStocktaking($type);
        $this->getRequest()->setParam(
            'id',
            $stocktaking->getId()
        );
        $this->getRequest()->setParam('createAdjustStock', true);
        $this->getRequest()->setParam('source_code', $stocktaking->getSourceCode());
    }

    /**
     * Get stocktaking to test
     *
     * @param int $type
     * @return StocktakingInterface
     */
    public function getStocktaking(int $type)
    {
        return $this->_objectManager->create(Collection::class)
            ->setOrder(StocktakingInterface::ID, Collection::SORT_ORDER_DESC)
            ->addFieldToFilter(StocktakingInterface::STOCKTAKING_TYPE, $type)
            ->getFirstItem();
    }

    /**
     * Get adjust stock
     *
     * @return AdjustStockInterface
     */
    public function getAdjustStock()
    {
        return $this->_objectManager->create(AdjustCollection::class)
            ->setOrder(AdjustStockInterface::ADJUSTSTOCK_ID, AdjustCollection::SORT_ORDER_DESC)
            ->getFirstItem();
    }

    /**
     * Get stocktaking archive to test
     *
     * @param int $type
     * @return StocktakingArchiveInterface
     */
    public function getStocktakingArchive(int $type)
    {
        return $this->_objectManager->create(ArchiveCollection::class)
            ->setOrder(StocktakingArchiveInterface::ID, ArchiveCollection::SORT_ORDER_DESC)
            ->addFieldToFilter(StocktakingArchiveInterface::STOCKTAKING_TYPE, $type)
            ->getFirstItem();
    }

    /**
     * Data provider for stocktaking
     *
     * @return array
     */
    public function stocktakingTypeDataProvider()
    {
        return [
            'Partial type' => [
                'Type' => StocktakingInterface::STOCKTAKING_TYPE_PARTIAL,
                'Expected Results' => [
                    'code' => 'ST00000001',
                    'source_code' => 'source-code-1',
                    'source_name' => 'source-name-1',
                    'product_sku' => ['SKU-2', 'SKU-3'],
                    'old_qty' => ['5', '6'],
                    'new_qty' => ['5', '7'],
                    'change_qty' => ['0', '1'],
                ]
            ],
            'Full type' => [
                'Type' => StocktakingInterface::STOCKTAKING_TYPE_FULL,
                'Expected Results' => [
                    'code' => 'ST00000002',
                    'source_code' => 'source-code-1',
                    'source_name' => 'source-name-1',
                    'product_sku' => ['SKU-2', 'SKU-3', 'SKU-1', 'SKU-5'],
                    'old_qty' => ['5', '6', '5.5', '2'],
                    'new_qty' => ['5', '7', '0', '0'],
                    'change_qty' => ['0', '1', '-5.5', '-2'],
                ]
            ]
        ];
    }
}
