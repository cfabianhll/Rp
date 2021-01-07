<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rma
 * @version   2.1.12
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rma\Repository;

use Mirasvit\Rma\Api\Data\QuickResponseInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Mirasvit\Rma\Model\QuickResponse as QuickResponseModel;
use Mirasvit\Rma\Model\ResourceModel\QuickResponse\Collection as QuickResponseModelCollection;

class QuickResponse implements \Mirasvit\Rma\Api\Repository\QuickResponseRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var QuickResponseModel[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\QuickResponse
     */
    private $responseResource;
    /**
     * @var \Mirasvit\Rma\Model\QuickResponseFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\QuickResponseSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * QuickResponse constructor.
     * @param \Mirasvit\Rma\Model\QuickResponseFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\QuickResponse $responseResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Data\QuickResponseSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\QuickResponseFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\QuickResponse $responseResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Data\QuickResponseSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->responseResource     = $responseResource;
        $this->storeManager         = $storeManager;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\QuickResponseInterface $response)
    {
        $this->responseResource->save($response);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function get($responseId)
    {
        if (!isset($this->instances[$responseId])) {
            /** @var QuickResponseModel $response */
            $response = $this->objectFactory->create();
            $response->load($responseId);
            if (!$response->getId()) {
                throw NoSuchEntityException::singleField('id', $responseId);
            }
            $this->instances[$responseId] = $response;
        }
        return $this->instances[$responseId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($name)
    {
        if (!isset($this->instances[$name])) {
            /** @var QuickResponseModel $response */
            $response = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter(QuickResponseInterface::KEY_NAME, $name)
                ->getFirstItem();

            if (!$response->getId()) {
                throw NoSuchEntityException::singleField(QuickResponseInterface::KEY_NAME, $name);
            }
            $this->instances[$name] = $response;
        }
        return $this->instances[$name];
    }

    /**
     * @param int|\Magento\Store\Model\Store $storeId
     * @return QuickResponseModelCollection
     */
    public function getListByStoreId($storeId)
    {
        $responseCollection = $this->objectFactory->create()->getCollection()
            ->addFieldToFilter(QuickResponseInterface::KEY_IS_ACTIVE, 1)
            ->addStoreFilter($storeId)
        ;

        return $responseCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\QuickResponseInterface $response)
    {
        try {
            $statusId = $response->getId();
            $this->responseResource->delete($response);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete Quick Response with id %1',
                    $response->getId()
                ),
                $e
            );
        }
        unset($this->instances[$statusId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($responseId)
    {
        $response = $this->get($responseId);

        return  $this->delete($response);
    }

    /**
     * Validate QuickResponse process
     *
     * @param QuickResponseModel $response
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateQuickResponse(QuickResponseModel $response)
    {

    }
}
