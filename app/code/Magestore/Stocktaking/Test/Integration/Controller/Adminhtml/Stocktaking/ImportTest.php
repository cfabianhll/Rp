<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Controller\Adminhtml\Stocktaking;

use Laminas\Stdlib\Parameters;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Api\StocktakingItemRepositoryInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;

/**
 * Class ImportTest
 *
 * Used for test import product to stocktaking
 * @magentoAppArea adminhtml
 */
class ImportTest extends AbstractBackendController
{
    /**
     * Url to stocktaking list
     *
     * @inheritDoc
     */
    protected $uri = 'backend/stocktaking/stocktaking/import';

    /**
     * @var string
     */
    protected $resource = 'Magestore_Stocktaking::edit_stocktaking';

    /**
     * @var string|null
     */
    protected $httpMethod = HttpRequest::METHOD_POST;
    /**
     * @var FormKey
     */
    protected $formkey;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var StocktakingItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * Setup environment
     *
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->formkey = $this->_objectManager->get(FormKey::class);
        $this->reader = $this->_objectManager->get(Reader::class);
        $this->stockItemRepository = $this->_objectManager->get(StocktakingItemRepositoryInterface::class);
    }

    /**
     * Test import with invalid sku
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_all_types.php
     */
    public function testImportWithInvalidSku()
    {
        $files = [
            'import_product' => [
                'name' => 'import_stocktaking.csv',
                'type' => 'text/csv',
                'tmp_name' => $this->reader->getModuleDir('', 'Magestore_Stocktaking')
                    .'/Test/_files/import_stocktaking_invalid_sku.csv',
                'error' => 0,
                'size' => 10,
            ],
        ];
        $this->prepareImportRequest($files);
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'Could not import products. Please try again.'
                ]
            ),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test import with valid sku
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_all_types.php
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryCatalog::Test/_files/source_items_on_default_source.php
     */
    public function testImportWithValidSku()
    {
        $files = [
            'import_product' => [
                'name' => 'import_stocktaking.csv',
                'type' => 'text/csv',
                'tmp_name' => $this->reader->getModuleDir('', 'Magestore_Stocktaking')
                    .'/Test/_files/import_stocktaking_valid_sku.csv',
                'error' => 0,
                'size' => 10,
            ],
        ];
        $this->prepareImportRequest($files);
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'You have updated 2 item(s) to stock-taking.'
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );
        $itemCollection = $this->stockItemRepository->getListByStocktakingId($this->getFirstStocktakingCountingId());

        $this->assertEquals(
            ['SKU-2', 'SKU-3'],
            $itemCollection->getColumnValues('product_sku')
        );

        $this->assertEquals(
            ['5', '6'],
            $itemCollection->getColumnValues('qty_in_source')
        );

        $this->assertEquals(
            ['5', '7'],
            $itemCollection->getColumnValues('counted_qty')
        );

        $this->assertEquals(
            [null, 'Mistake'],
            $itemCollection->getColumnValues('difference_reason')
        );
    }

    /**
     * Test import with invalid files
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_all_types.php
     */
    public function testImportWithInvalidFiles()
    {
        $files = [
            'import_product' => [],
        ];
        $this->prepareImportRequest($files);
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'Invalid file upload attempt'
                ]
            ),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $this->prepareRequest();
        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $this->prepareRequest();
        parent::testAclNoAccess();
    }

    /**
     * Get first stocktaking id
     *
     * @return int|null
     */
    public function getFirstStocktakingCountingId()
    {
        $firstStocktaking = $this->_objectManager->create(Collection::class)
            ->addFieldToFilter(StocktakingInterface::STATUS, StocktakingInterface::STATUS_COUNTING)
            ->getFirstItem();
        return $firstStocktaking->getId();
    }

    /**
     * Prepare import request files
     *
     * @param array $files
     */
    public function prepareImportRequest(array $files)
    {
        $this->prepareRequest();
        $this->getRequest()->setParam('id', $this->getFirstStocktakingCountingId());
        $this->getRequest()->setFiles(new Parameters($files));
        $this->dispatch($this->uri);
    }

    /**
     * Add form key to request
     */
    public function prepareRequest()
    {
        $params = [];
        $params['form_key'] = $this->formkey->getFormKey();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($params);
    }
}
