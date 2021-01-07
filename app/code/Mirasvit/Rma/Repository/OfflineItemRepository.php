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

class OfflineItemRepository implements \Mirasvit\Rma\Api\Repository\OfflineItemRepositoryInterface
{
    use \Mirasvit\Rma\Repository\RepositoryFunction\Create;
    use \Mirasvit\Rma\Repository\RepositoryFunction\GetList;

    /**
     * @var \Mirasvit\Rma\Model\OfflineItem[]
     */
    protected $instances = [];
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\OfflineItem\CollectionFactory
     */
    private $offlineItemCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\OfflineItem
     */
    private $offlineItemResource;
    /**
     * @var \Mirasvit\Rma\Model\OfflineItemFactory
     */
    private $objectFactory;
    /**
     * @var \Mirasvit\Rma\Api\Data\OfflineItemSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * OfflineItemRepository constructor.
     * @param \Mirasvit\Rma\Model\OfflineItemFactory $objectFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\OfflineItem $offlineItemResource
     * @param \Mirasvit\Rma\Model\ResourceModel\OfflineItem\CollectionFactory $offlineItemCollectionFactory
     * @param \Mirasvit\Rma\Api\Data\OfflineItemSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\OfflineItemFactory $objectFactory,
        \Mirasvit\Rma\Model\ResourceModel\OfflineItem $offlineItemResource,
        \Mirasvit\Rma\Model\ResourceModel\OfflineItem\CollectionFactory $offlineItemCollectionFactory,
        \Mirasvit\Rma\Api\Data\OfflineItemSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory                = $objectFactory;
        $this->offlineItemResource          = $offlineItemResource;
        $this->searchResultsFactory         = $searchResultsFactory;
        $this->offlineItemCollectionFactory = $offlineItemCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Mirasvit\Rma\Api\Data\OfflineItemInterface $offlineItem)
    {
        $this->offlineItemResource->save($offlineItem);

        return $offlineItem;
    }

    /**
     * {@inheritdoc}
     */
    public function get($offlineItemId)
    {
        if (!isset($this->instances[$offlineItemId])) {
            /** @var \Mirasvit\Rma\Model\OfflineItem $offlineItem */
            $offlineItem = $this->objectFactory->create();
            $offlineItem->getResource()->load($offlineItem, $offlineItemId);
            if (!$offlineItem->getId()) {
                throw NoSuchEntityException::singleField('id', $offlineItemId);
            }
            $this->instances[$offlineItemId] = $offlineItem;
        }

        return $this->instances[$offlineItemId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Mirasvit\Rma\Api\Data\OfflineItemInterface $offlineItem)
    {
        try {
            $offlineItemId = $offlineItem->getId();
            $this->offlineItemResource->delete($offlineItem);
        } catch (\Exception $e) {
            throw new StateException(
                __(
                    'Cannot delete item with id %1',
                    $offlineItem->getId()
                ),
                $e
            );
        }
        unset($this->instances[$offlineItemId]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($itemId)
    {
        $item = $this->get($itemId);
        return  $this->delete($item);
    }

    /**
     * Validate item process
     *
     * @param  \Mirasvit\Rma\Model\OfflineItem $item
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateItem(\Mirasvit\Rma\Model\OfflineItem $item)
    {

    }

    /**
     * @return \Mirasvit\Rma\Model\ResourceModel\OfflineItem\Collection
     */
    public function getCollection()
    {
        return $this->offlineItemCollectionFactory->create();
    }
}
