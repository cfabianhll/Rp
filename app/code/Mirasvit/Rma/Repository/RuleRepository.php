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
use Mirasvit\Rma\Model\Rule;

class RuleRepository implements \Mirasvit\Rma\Api\Repository\RuleRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var Rule[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Rule
     */
    private $ruleResource;
    /**
     * @var \Mirasvit\Rma\Model\RuleFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\RuleSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * RuleRepository constructor.
     * @param \Mirasvit\Rma\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param \Mirasvit\Rma\Model\RuleFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\Rule $ruleResource
     * @param \Mirasvit\Rma\Api\Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Mirasvit\Rma\Model\RuleFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\Rule $ruleResource,
        \Mirasvit\Rma\Api\Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->objectFactory         = $objectFactory;
        $this->ruleResource          = $ruleResource;
        $this->searchResultsFactory  = $searchResultsFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\RuleInterface $rule)
    {
        $this->ruleResource->save($rule);

        return $rule;
    }

    /**
     * {@inheritdoc}
     */
    public function get($ruleId)
    {
        if (!isset($this->instances[$ruleId])) {
            /** @var Rule $rule */
            $rule = $this->objectFactory->create();
            $rule->load($ruleId);
            if (!$rule->getId()) {
                throw NoSuchEntityException::singleField('id', $ruleId);
            }
            $this->instances[$ruleId] = $rule;
        }

        return $this->instances[$ruleId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\RuleInterface $rule)
    {
        try {
            $ruleId = $rule->getId();
            $this->ruleResource->delete($rule);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete rule with id %1',
                    $rule->getId()
                ),
                $e
            );
        }
        unset($this->instances[$ruleId]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($ruleId)
    {
        $rule = $this->get($ruleId);

        return  $this->delete($rule);
    }

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\Rule\Collection
     */
    public function getCollection()
    {
        return $this->ruleCollectionFactory->create();
    }
}
