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



namespace Mirasvit\Rma\Helper\Item;


class Option extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface
     */
    private $resolutionRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface
     */
    private $reasonRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface
     */
    private $conditionRepository;
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    private $context;

    /**
     * Option constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface $conditionRepository
     * @param \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface $reasonRepository
     * @param \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface $conditionRepository,
        \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface $reasonRepository,
        \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->conditionRepository = $conditionRepository;
        $this->reasonRepository = $reasonRepository;
        $this->resolutionRepository = $resolutionRepository;
        $this->context = $context;
        parent::__construct($context);
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ConditionInterface[]
     */
    public function getConditionList()
    {
        $sortOrderSort = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection( \Magento\Framework\Api\SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addSortOrder($sortOrderSort)
        ;

        return $this->conditionRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ReasonInterface[]
     */
    public function getReasonList()
    {
        $sortOrderSort = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection( \Magento\Framework\Api\SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addSortOrder($sortOrderSort)
        ;

        return $this->reasonRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * @return \Mirasvit\Rma\Api\Data\ResolutionInterface[]
     */
    public function getResolutionList()
    {
        $sortOrderSort = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setDirection( \Magento\Framework\Api\SortOrder::SORT_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
            ->addSortOrder($sortOrderSort)
        ;

        return $this->resolutionRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * @return array
     */
    public function getConditionOptionArray()
    {
        $array = [];
        $conditions = $this->getConditionList();
        /** @var \Mirasvit\Rma\Api\Data\ConditionInterface $condition */
        foreach ($conditions as $condition) {
            $array[$condition->getId()] = $condition->getName();
        }

        return $array;
    }

    /**
     * @return array
     */
    public function getReasonOptionArray()
    {
        $array = [];
        $reasons = $this->getReasonList();
        /** @var \Mirasvit\Rma\Api\Data\ReasonInterface $reason */
        foreach ($reasons as $reason) {
            $array[$reason->getId()] = $reason->getName();
        }

        return $array;
    }

    /**
     * @return array
     */
    public function getResolutionOptionArray()
    {
        $array = [];
        $resolutions = $this->getResolutionList();
        /** @var \Mirasvit\Rma\Api\Data\ResolutionInterface $resolution */
        foreach ($resolutions as $resolution) {
            $array[$resolution->getId()] = $resolution->getName();
        }

        return $array;
    }
}