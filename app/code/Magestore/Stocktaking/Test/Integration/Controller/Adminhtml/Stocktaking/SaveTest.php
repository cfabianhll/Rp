<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magestore\Stocktaking\Test\Integration\Controller\Adminhtml\Stocktaking;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magestore\Stocktaking\Model\ResourceModel\Stocktaking\Collection;
use Magestore\Stocktaking\Api\Data\StocktakingInterface;

/**
 * Class SaveTest
 *
 * Used for test save stocktaking
 * @magentoAppArea adminhtml
 */
class SaveTest extends AbstractBackendController
{
    /**
     * Stub stocktaking
     */
    const STUB_STOCKTAKING_DATA = [
        StocktakingInterface::SOURCE_CODE => 'default',
        StocktakingInterface::ASSIGN_USER_ID => 1,
        StocktakingInterface::CREATED_AT => '2020-09-09',
        StocktakingInterface::DESCRIPTION => 'Stocktaking'
    ];

    /**
     * Url to save
     *
     * @inheritDoc
     */
    protected $uri = 'backend/stocktaking/stocktaking/save';

    /**
     * @var FormKey
     */
    protected $formkey;

    /**
     * @var UrlInterface
     */
    protected $backendModel;

    /**
     * @var string
     */
    protected $resource = 'Magestore_Stocktaking::create_stocktaking';

    /**
     * Setup environment
     *
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->backendModel = $this->_objectManager->get(UrlInterface::class);
        $this->formkey = $this->_objectManager->get(FormKey::class);
    }

    /**
     * Test save with data provider
     *
     * @param int $stocktakingType
     * @throws \Magento\Framework\Exception\LocalizedException
     * @dataProvider stocktakingDataProvider
     */
    public function testSaveWithCorrectInformation(int $stocktakingType)
    {
        $this->prepareRequest($stocktakingType);
        $this->dispatch($this->uri);
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'The stock-taking has been saved successfully'
                ]
            ),
            MessageInterface::TYPE_SUCCESS
        );
        $stocktakingSaved = $this->_objectManager->get(Collection::class)
            ->setOrder(StocktakingInterface::ID, 'DESC')
            ->getFirstItem();
        $url = $this->backendModel->getUrl(
            'stocktaking/stocktaking/edit',
            [
                'id' => $stocktakingSaved->getId()
            ]
        );
        $this->assertRedirect($this->stringStartsWith($url));
        $this->assertEquals(
            self::STUB_STOCKTAKING_DATA[StocktakingInterface::SOURCE_CODE],
            $stocktakingSaved->getSourceCode()
        );
        $this->assertEquals(
            self::STUB_STOCKTAKING_DATA[StocktakingInterface::ASSIGN_USER_ID],
            $stocktakingSaved->getAssignUserId()
        );
        $this->assertEquals(
            $stocktakingType,
            $stocktakingSaved->getStocktakingType()
        );
        $this->assertEquals(
            self::STUB_STOCKTAKING_DATA[StocktakingInterface::CREATED_AT],
            $stocktakingSaved->getCreatedAt()
        );
        $this->assertEquals(
            self::STUB_STOCKTAKING_DATA[StocktakingInterface::DESCRIPTION],
            $stocktakingSaved->getDescription()
        );
        //rollback data
        $stocktakingSaved->delete();
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
     * Prepare request to save
     *
     * @param int $stocktakingType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareRequest(int $stocktakingType = StocktakingInterface::STOCKTAKING_TYPE_PARTIAL)
    {
        $params = self::STUB_STOCKTAKING_DATA;
        $params[StocktakingInterface::STOCKTAKING_TYPE] = $stocktakingType;
        $params['form_key'] = $this->formkey->getFormKey();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($params);
    }

    /**
     * Stocktaking data provider partial and full
     *
     * @return array
     */
    public function stocktakingDataProvider()
    {
        return [
            [
                StocktakingInterface::STOCKTAKING_TYPE_PARTIAL
            ],
            [
                StocktakingInterface::STOCKTAKING_TYPE_FULL
            ]
        ];
    }
}
