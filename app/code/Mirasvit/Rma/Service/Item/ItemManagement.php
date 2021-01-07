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


namespace Mirasvit\Rma\Service\Item;

/**
 *  We put here only methods directly connected with Item properties
 */
class ItemManagement implements \Mirasvit\Rma\Api\Service\Item\ItemManagementInterface
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $itemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface
     */
    private $resolutionRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface
     */
    private $reasonRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface
     */
    private $conditionRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface
     */
    private $rmaManagement;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * ItemManagement constructor.
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository
     * @param \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository
     * @param \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface $reasonRepository
     * @param \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface $conditionRepository
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Repository\ResolutionRepositoryInterface $resolutionRepository,
        \Mirasvit\Rma\Api\Repository\ReasonRepositoryInterface $reasonRepository,
        \Mirasvit\Rma\Api\Repository\ConditionRepositoryInterface $conditionRepository,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface $rmaManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->itemRepository        = $itemRepository;
        $this->resolutionRepository  = $resolutionRepository;
        $this->reasonRepository      = $reasonRepository;
        $this->conditionRepository   = $conditionRepository;
        $this->rmaManagement         = $rmaManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderItemRepository   = $orderItemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderItem(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        if (!isset($this->items[$item->getOrderItemId()])) {
            $this->items[$item->getOrderItemId()] = $this->orderItemRepository->get($item->getOrderItemId());
        }

        return $this->items[$item->getOrderItemId()];
    }

    /**
     * {@inheritdoc}
     */
    public function isExchange(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', 1)
        ;

        $resolutions = $this->resolutionRepository->getList($searchCriteria->create())->getItems();
        $allowed = false;
        /** @var \Mirasvit\Rma\Api\Data\ResolutionInterface $resolution */
        foreach ($resolutions as $resolution) {
            if ($item->getResolutionId() == $resolution->getId() && $resolution->getExchangeOrderEnabled()) {
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredit(\Mirasvit\Rma\Api\Data\ItemInterface $item)
    {
        $resolution = $this->resolutionRepository->getByCode(\Mirasvit\Rma\Api\Data\ResolutionInterface::CREDIT);

        return $item->getResolutionId() == $resolution->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getResolutionName($item)
    {
        if ($item->getResolutionId()) {
            return $this->resolutionRepository->get($item->getResolutionId())->getName();
        } else {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonName($item)
    {
        if ($item->getReasonId()) {
            return $this->reasonRepository->get($item->getReasonId())->getName();
        } else {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionName($item)
    {
        if ($item->getConditionId()) {
            return $this->conditionRepository->get($item->getConditionId())->getName();
        } else {
            return '';
        }
    }
}