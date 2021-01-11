<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveInterface;
use Magestore\Stocktaking\Api\Data\StocktakingArchiveItemInterface;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\Collection as StocktakingArchiveCollection;
use Magestore\Stocktaking\Model\ResourceModel\StocktakingArchive\StocktakingArchiveItem\Collection
    as StocktakingArchiveItemCollection;

/**
 * Class CancelTest
 *
 * Used for test cancel stocktaking
 * @magentoAppArea adminhtml
 */
class CancelTest extends AbstractBackendController
{

    /**
     * Url to cancel
     */
    protected $uri = 'backend/stocktaking/stocktaking/cancel';

    /**
     * @var string
     */
    protected $resource = 'Magestore_Stocktaking::cancel';

    /**
     * Test Cancel Stocktaking
     *
     * @param int $stocktakingId
     * @param array $expectedResult
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryCatalog::Test/_files/source_items_on_default_source.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_partial_with_products.php
     * @dataProvider requestDataProvider
     */
    public function testCancelStocktaking(int $stocktakingId, array $expectedResult)
    {
        $id = $this->prepareRequest($stocktakingId);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    $expectedResult['message']
                ]
            ),
            $expectedResult['messageType']
        );

        if (isset($expectedResult['archiveStocktakingData'])) {
            $archiveStocktaking = $this->_objectManager->get(StocktakingArchiveCollection::class)
                ->setOrder(StocktakingArchiveInterface::ID, StocktakingArchiveCollection::SORT_ORDER_DESC)
                ->getFirstItem();
            foreach ($expectedResult['archiveStocktakingData'] as $key => $value) {
                $this->assertEquals(
                    $value,
                    $archiveStocktaking->getData($key)
                );
            }

            /** @var StocktakingArchiveItemCollection $stocktakingArchiveItemCollection */
            $stocktakingArchiveItemCollection = $this->_objectManager->create(StocktakingArchiveItemCollection::class)
                ->addFieldToFilter(
                    StocktakingArchiveItemInterface::STOCKTAKING_ID,
                    $id
                );
            $this->assertEquals(
                count($stocktakingArchiveItemCollection),
                $expectedResult['stocktakingArchiveItemsCount']
            );
        }
    }

    /**
     * Prepare request to save
     *
     * @param int $stocktakingId
     * @return int
     */
    public function prepareRequest(int $stocktakingId = 0)
    {
        if ($stocktakingId > 0) {
            $id = $stocktakingId;
        } else {
            /** @var StocktakingInterface $stocktaking */
            $stocktaking = $this->_objectManager->create(Collection::class)
                ->setOrder(StocktakingInterface::ID, Collection::SORT_ORDER_DESC)
                ->getFirstItem();
            $id = $stocktaking->getId();
        }
        $this->getRequest()->setParams(['_secure' => true, 'id'=> $id]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        return $id;
    }

    /**
     * Request data provider
     *
     * @return array
     */
    public function requestDataProvider(): array
    {
        return [
            'fail' => [
                'stocktakingId' => 99999,
                'expectedResult' => [
                    'message' => 'The stock-taking cannot be canceled.',
                    'messageType' => MessageInterface::TYPE_ERROR
                ]
            ],
            'success' => [
                'stocktakingId' => 0,
                'expectedResult' => [
                    'message' => 'You have canceled the stock-taking successfully.',
                    'messageType' => MessageInterface::TYPE_SUCCESS,
                    'archiveStocktakingData' => [
                        StocktakingArchiveInterface::CODE => 'ST00000001',
                        StocktakingArchiveInterface::STATUS => StocktakingInterface::STATUS_CANCELED
                    ],
                    'stocktakingArchiveItemsCount' => 3
                ]
            ]
        ];
    }
}
