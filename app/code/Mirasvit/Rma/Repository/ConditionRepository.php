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
use Mirasvit\Rma\Model\Condition;

class ConditionRepository implements \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Condition[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Condition
     */
    private $conditionResource;
    /**
     * @var \Mirasvit\Rma\Model\ConditionFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\ConditionSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * ConditionRepository constructor.
     * @param \Mirasvit\Rma\Model\ConditionFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Condition $conditionResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mirasvit\Rma\Api\Data\ConditionSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ConditionFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Condition $conditionResource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mirasvit\Rma\Api\Data\ConditionSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->conditionResource    = $conditionResource;
        $this->storeManager         = $storeManager;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\ConditionInterface $condition)
    {
        $this->conditionResource->save($condition);
        return $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function get($conditionId)
    {
        if (!isset($this->instances[$conditionId])) {
            /** @var Condition $condition */
            $condition = $this->objectFactory->create();
            $condition->load($conditionId);
            if (!$condition->getId()) {
                throw NoSuchEntityException::singleField('id', $conditionId);
            }
            $this->instances[$conditionId] = $condition;
        }
        return $this->instances[$conditionId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\ConditionInterface $condition)
    {
        try {
            $conditionId = $condition->getId();
            $this->conditionResource->delete($condition);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete condition with id %1',
                    $condition->getId()
                ),
                $e
            );
        }
        unset($this->instances[$conditionId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($conditionId)
    {
        $condition = $this->get($conditionId);
        return  $this->delete($condition);
    }

    /**
     * Validate condition process
     *
     * @param  Condition $condition
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateCondition(Condition $condition)
    {

    }
}
