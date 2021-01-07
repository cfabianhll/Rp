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

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException as ModelException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

use Mirasvit\Rma\Model\Rma;
use Mirasvit\Rma\Model\Reason;

class ReasonRepository implements \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Reason[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Reason
     */
    private $reasonResource;
    /**
     * @var \Mirasvit\Rma\Model\ReasonFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\ReasonSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * ReasonRepository constructor.
     * @param \Mirasvit\Rma\Model\ReasonFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Reason $reasonResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Data\ReasonSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ReasonFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Reason $reasonResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Data\ReasonSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->reasonResource       = $reasonResource;
        $this->storeManager         = $storeManager;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\ReasonInterface $reason)
    {
        $this->reasonResource->save($reason);
        return $reason;
    }

    /**
     * {@inheritdoc}
     */
    public function get($reasonId)
    {
        if (!isset($this->instances[$reasonId])) {
            /** @var Reason $reason */
            $reason = $this->objectFactory->create();
            $reason->load($reasonId);
            if (!$reason->getId()) {
                throw NoSuchEntityException::singleField('id', $reasonId);
            }
            $this->instances[$reasonId] = $reason;
        }
        return $this->instances[$reasonId];
    }

    /**
     * @param string $code
     * @return Reason
     * @throws NoSuchEntityException
     */
    public function getByCode($code)
    {
        if (!isset($this->instances[$code])) {
            /** @var Reason $reason */
            $reason = $this->objectFactory->create()->getCollection()
                ->addFieldToFilter('code', $code)
                ->getFirstItem();

            if (!$reason->getId()) {
                throw NoSuchEntityException::singleField('code', $code);
            }
            $this->instances[$code] = $reason;
        }
        return $this->instances[$code];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\ReasonInterface $reason)
    {
        try {
            $reasonId = $reason->getId();
            $this->reasonResource->delete($reason);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete reason with id %1',
                    $reason->getId()
                ),
                $e
            );
        }
        unset($this->instances[$reasonId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($reasonId)
    {
        $reason = $this->get($reasonId);
        return  $this->delete($reason);
    }

    /**
     * Validate reason process
     *
     * @param  Reason $reason
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateReason(Reason $reason)
    {

    }
}
