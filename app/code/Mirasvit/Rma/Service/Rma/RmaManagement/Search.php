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


namespace Mirasvit\Rma\Service\Rma\RmaManagement;

use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 *  We put here only methods directly connected with RMA properties
 */
class Search implements \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
{

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface
     */
    private $messageRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface
     */
    private $itemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface
     */
    private $offlineItemRepository;
    /**
     * @var \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface
     */
    private $attachmentManagement;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * Search constructor.
     * @param \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
     * @param \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository
     * @param \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository
     * @param \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface $offlineItemRepository
     * @param \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder,
        \Mirasvit\Rma\Api\Repository\MessageRepositoryInterface $messageRepository,
        \Mirasvit\Rma\Api\Repository\ItemRepositoryInterface $itemRepository,
        \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface $offlineItemRepository,
        \Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface $attachmentManagement,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->sortOrderBuilder      = $sortOrderBuilder;
        $this->messageRepository     = $messageRepository;
        $this->itemRepository        = $itemRepository;
        $this->offlineItemRepository = $offlineItemRepository;
        $this->attachmentManagement  = $attachmentManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
        ;
        return $this->itemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaItemsByOrderItem($orderItemId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_item_id', $orderItemId)
            ->addFilter('qty_requested', 0, 'gt')
        ;

        return $this->itemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedItems(RmaInterface $rma)
    {
        $searchCriteria = $this->getItemsSearchCriteria($rma);

        return $this->itemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestedOfflineItems(RmaInterface $rma)
    {
        $searchCriteria = $this->getItemsSearchCriteria($rma);

        return $this->offlineItemRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * @param RmaInterface $rma
     * @return \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private function getItemsSearchCriteria(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('qty_requested')
                    ->setConditionType('gt')
                    ->setValue(0)
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        );

        return $searchCriteria;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages(RmaInterface $rma)
    {
        $order = $this->sortOrderBuilder
            ->setField('message_id')
            ->setDirection(\Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addSortOrder($order)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastMessage(RmaInterface $rma)
    {
        $messages = $this->getMessages($rma);

        return array_pop($messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerRead(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('main_table.user_id', 0, 'gt')
            ->addFilter('is_read', 1)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserRead(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('main_table.customer_id', 0, 'gt')
            ->addFilter('is_read', 1)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerUnread(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('main_table.user_id', 0, 'gt')
            ->addFilter('is_read', 0)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserUnread(RmaInterface $rma)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rma->getId())
            ->addFilter('main_table.customer_id', 0, 'gt')
            ->addFilter('is_read', 0)
        ;

        return $this->messageRepository->getList($searchCriteria->create())->getItems();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRmaReason(RmaInterface $rma, $reasonId)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('main_table.reason_id')
                    ->setValue($reasonId)
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getTotalCount();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRmaCondition(RmaInterface $rma, $conditionId)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('main_table.condition_id')
                    ->setValue($conditionId)
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getTotalCount();
    }

    /**
     * {@inheritdoc}
     */
    public function hasRmaResolution(RmaInterface $rma, $resolutionId)
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('rma_id')
                    ->setValue($rma->getId())
                    ->create(),
            ]
        )->addFilters(
            [
                $this->filterBuilder
                    ->setField('main_table.resolution_id')
                    ->setValue($resolutionId)
                    ->create(),
            ]
        );

        return $this->itemRepository->getList($searchCriteria->create())->getTotalCount();
    }
}

