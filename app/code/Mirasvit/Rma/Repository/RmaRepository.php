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
use Mirasvit\Rma\Repository;

class RmaRepository implements \Mirasvit\Rma\Api\Repository\RmaRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var \Mirasvit\Rma\Model\Rma[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    private $rmaCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Rma
     */
    private $rmaResource;
    /**
     * @var \Mirasvit\Rma\Model\RmaFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * RmaRepository constructor.
     * @param \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
     * @param \Mirasvit\Rma\Model\RmaFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Rma $rmaResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        \Mirasvit\Rma\Model\RmaFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Rma $rmaResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Data\RmaSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->objectFactory        = $objectFactory;
        $this->rmaResource          = $rmaResource;
        $this->storeManager         = $storeManager;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        $this->rmaResource->save($rma);

        return $rma;
    }

    /**
     * {@inheritdoc}
     */
    public function get($rmaId)
    {
        if (!isset($this->instances[$rmaId])) {
            /** @var \Mirasvit\Rma\Model\Rma $rma */
            $rma = $this->objectFactory->create();
            $rma->load($rmaId);
            if (!$rma->getId()) {
                throw NoSuchEntityException::singleField('id', $rmaId);
            }
            $this->instances[$rmaId] = $rma;
        }

        return $this->instances[$rmaId];
    }


    /**
     * {@inheritdoc}
     */
    public function getByGuestId($guestId)
    {
        if (!isset($this->instances[$guestId])) {
            /** @var \Mirasvit\Rma\Model\Rma $rma */
            $rma = $this->objectFactory->create()->getCollection()
                    ->addFieldToFilter('guest_id', $guestId)
                    ->getFirstItem();

            if (!$rma->getId()) {
                throw NoSuchEntityException::singleField('guest_id', $guestId);
            }
            $this->instances[$guestId] = $rma;
        }

        return $this->instances[$guestId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\RmaInterface $rma)
    {
        try {
            $rmaId = $rma->getId();
            $this->rmaResource->delete($rma);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete rma with id %1',
                    $rma->getId()
                ),
                $e
            );
        }
        unset($this->instances[$rmaId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($rmaId)
    {
        $rma = $this->get($rmaId);
        return  $this->delete($rma);
    }

    /**
     * Validate rma process
     *
     * @param  \Mirasvit\Rma\Model\Rma $rma
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateRma(\Mirasvit\Rma\Model\Rma $rma)
    {

    }

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\Rma\Collection
     */
    public function getCollection()
    {
        return $this->rmaCollectionFactory->create();
    }

}
