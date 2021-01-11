<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Class IndexTest
 *
 * Used for test stocktaking list
 * @magentoAppArea adminhtml
 */
class IndexTest extends AbstractBackendController
{
    /**
     * Url to stocktaking list
     *
     * @inheritDoc
     */
    protected $uri = 'backend/stocktaking/stocktaking/index';

    /**
     * @var string
     */
    protected $resource = 'Magestore_Stocktaking::view_listing';

    /**
     * Test list stocktaking
     *
     * @param array $filters
     * @param array $sort
     * @param int $expectedRows
     * @param array $expectedCodes
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_all_types.php
     * @dataProvider queryDataProvider
     */
    public function testListStocktakingWithFilter(
        array $filters,
        array $sort,
        int $expectedRows,
        array $expectedCodes
    ) {
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $url = 'backend/mui/index/render/?namespace=ms_stocktaking_listing';
        foreach ($filters as $filter) {
            $url .= '&filters%5B'.$filter['filter_field'].'%5D='.$filter['filter_value'];
        }
        $url .= '&paging%5BpageSize%5D=20';
        $url .= '&paging%5Bcurrent%5D=1';
        $url .= '&sorting%5Bfield%5D='.$sort['field'];
        $url .= '&sorting%5Bdirection%5D='.$sort['value'];
        $url .= '&isAjax=1';

        $this->getRequest()->getHeaders()->addHeaderLine('Accept', 'application/json');
        $this->dispatch($url);
        $this->assertNotEmpty($contentType = $this->getResponse()->getHeader('Content-Type'));
        $this->assertEquals('application/json', $contentType->getFieldValue());
        $data = json_decode($this->getResponse()->getBody(), true);
        $this->assertEquals($expectedRows, $data['totalRecords']);
        $codeList = array_column($data['items'], 'code');
        $this->assertEquals($expectedCodes, $codeList);
    }

    /**
     * Gets list of variations with different search queries.
     *
     * @return array
     */
    public function queryDataProvider(): array
    {
        return [
            'Filter stocktaking type and sort id desc' => [
                'filters' => [
                    [
                        'filter_field' => 'stocktaking_type',
                        'filter_value' => StocktakingInterface::STOCKTAKING_TYPE_PARTIAL
                    ]
                ],
                'sort' => [
                    'field' => 'id',
                    'value' => 'desc'
                ],
                'expectedRows' => 1,
                'expectedCodes' => ['ST00000002']
            ],
            'Filter stocktaking code and sort created at desc' => [
                'filters' => [
                    [
                        'filter_field' => 'code',
                        'filter_value' => '0000'
                    ]
                ],
                'sort' => [
                    'field' => 'created_at',
                    'value' => 'desc'
                ],
                'expectedRows' => 2,
                'expectedCodes' => ['ST00000002', 'ST00000001']
            ],
            'Filter stocktaking code and stocktaking status' => [
                'filters' => [
                    [
                        'filter_field' => 'code',
                        'filter_value' => '0000'
                    ],
                    [
                        'filter_field' => 'status',
                        'filter_value' => StocktakingInterface::STATUS_PREPARING
                    ],
                ],
                'sort' => [
                    'field' => 'id',
                    'value' => 'desc'
                ],
                'expectedRows' => 1,
                'expectedCodes' => ['ST00000001']
            ],
            'Filter with no result' => [
                'filters' => [
                    [
                        'filter_field' => 'code',
                        'filter_value' => 'test'
                    ]
                ],
                'sort' => [
                    'field' => 'created_at',
                    'value' => 'desc'
                ],
                'expectedRows' => 0,
                'expectedCodes' => []
            ]
        ];
    }
}
