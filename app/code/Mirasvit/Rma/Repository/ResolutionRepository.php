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

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

use Mirasvit\Rma\Api\Data\ResolutionInterface;
use Mirasvit\Rma\Model\Rma;
use Mirasvit\Rma\Model\Resolution;

class ResolutionRepository implements \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Resolution[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Resolution\CollectionFactory
     */
    private $resolutionCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Resolution
     */
    private $resolutionResource;
    /**
     * @var \Mirasvit\Rma\Model\ResolutionFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\ResolutionSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * ResolutionRepository constructor.
     * @param \Mirasvit\Rma\Model\ResolutionFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Resolution $resolutionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Model\ResourceModel\Resolution\CollectionFactory $resolutionCollectionFactory
     * @param \Mirasvit\Rma\Api\Data\ResolutionSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResolutionFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Resolution $resolutionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Model\ResourceModel\Resolution\CollectionFactory $resolutionCollectionFactory,
        \Mirasvit\Rma\Api\Data\ResolutionSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory               = $objectFactory;
        $this->resolutionResource          = $resolutionResource;
        $this->storeManager                = $storeManager;
        $this->resolutionCollectionFactory = $resolutionCollectionFactory;
        $this->searchResultsFactory        = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\ResolutionInterface $resolution)
    {
        $this->resolutionResource->save($resolution);
        return $resolution;
    }

    /**
     * {@inheritdoc}
     */
    public function get($resolutionId)
    {
        if (!isset($this->instances[$resolutionId])) {
            /** @var Resolution $resolution */
            $resolution = $this->objectFactory->create();
            $resolution->load($resolutionId);
            if (!$resolution->getId()) {
                throw NoSuchEntityException::singleField('id', $resolutionId);
            }
            $this->instances[$resolutionId] = $resolution;
        }
        return $this->instances[$resolutionId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByCode($code)
    {
        if (!isset($this->instances[$code])) {
            /** @var \Mirasvit\Rma\Model\Resolution $resolution */
            $resolution = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter(ResolutionInterface::KEY_CODE, $code)
                ->getFirstItem();

            if (!$resolution->getId()) {
                throw NoSuchEntityException::singleField(ResolutionInterface::KEY_CODE, $code);
            }
            $this->instances[$code] = $resolution;
        }
        return $this->instances[$code];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\ResolutionInterface $resolution)
    {
        try {
            $resolutionId = $resolution->getId();
            $this->resolutionResource->delete($resolution);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete resolution with id %1',
                    $resolution->getId()
                ),
                $e
            );
        }
        unset($this->instances[$resolutionId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($resolutionId)
    {
        $resolution = $this->get($resolutionId);
        return  $this->delete($resolution);
    }

    /**
     * Validate resolution process
     *
     * @param  Resolution $resolution
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateResolution(Resolution $resolution)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getCollection()
    {
        return $this->resolutionCollectionFactory->create();
    }
}
