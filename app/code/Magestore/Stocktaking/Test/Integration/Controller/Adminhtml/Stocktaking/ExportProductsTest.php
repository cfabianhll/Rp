<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Controller\Adminhtml\Stocktaking;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;

/**
 * Class ExportProductsTest
 *
 * Used for test export products of stocktaking
 * @magentoAppArea adminhtml
 */
class ExportProductsTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Magestore_Stocktaking::edit_stocktaking';

    /**
     * Url to cancel
     */
    protected $uri = 'backend/stocktaking/stocktaking/exportProducts';

    /**
     * @var Csv
     */
    protected $csvProcessor;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->csvProcessor = $this->_objectManager->get(Csv::class);
        $this->filesystem = $this->_objectManager->get(Filesystem::class);
    }

    /**
     * Test Export Products
     *
     * @param array $expectedResult
     * @throws \Magento\Framework\Exception\FileSystemException
     *
     * @magentoDbIsolation disabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento_InventoryApi::Test/_files/products.php
     * @magentoDataFixture Magento_InventoryCatalog::Test/_files/source_items_on_default_source.php
     * @magentoDataFixture Magestore_Stocktaking::Test/_files/stocktaking_partial_with_products.php
     * @dataProvider exportProductsDataProvider
     */
    public function testExportProducts(array $expectedResult)
    {
        $this->prepareRequest();
        $this->dispatch($this->uri);

        // Assert content of csv file
        $filename = $this->filesystem
            ->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->getAbsolutePath('magestore/stocktaking/stocktaking_products_list.csv');
        $content = $this->csvProcessor->getData($filename);
        $this->assertEquals(
            $expectedResult,
            $content,
            "File's content is wrong"
        );
    }

    /**
     * Prepare request
     */
    public function prepareRequest()
    {
        /** @var StocktakingInterface $stocktaking */
        $stocktaking = $this->_objectManager->create(Collection::class)
            ->setOrder(StocktakingInterface::ID, Collection::SORT_ORDER_DESC)
            ->getFirstItem();
        $this->getRequest()->setParams(['_secure' => true, 'id'=> $stocktaking->getId()]);
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
    }

    /**
     * Test Export Products Data Provider
     *
     * @return array
     */
    public function exportProductsDataProvider(): array
    {
        return [
            "Test success" => [
                'expectedResult' => [
                    ["SKU", "COUNTED QUANTITY", "REASON OF DIFFERENCE"],
                    ["SKU-1", "10.5", "Difference reason 1"],
                    ["SKU-2", "5", ""],
                    ["SKU-3", "", ""]
                ]
            ]
        ];
    }
}
