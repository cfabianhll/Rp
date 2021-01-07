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


namespace Mirasvit\Rma\Service\Report\Type;

class ReasonCnt
{
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory
     */
    private $itemCollectionFactory;
    /**
     * @var \Mirasvit\Rma\Model\ResourceModel\OfflineItem\CollectionFactory
     */
    private $offlineitemCollectionFactory;

    /**
     * ReasonCnt constructor.
     * @param \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     * @param \Mirasvit\Rma\Model\ResourceModel\OfflineItem\CollectionFactory $offlineitemCollectionFactory
     */
    public function __construct(
        \Mirasvit\Rma\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Mirasvit\Rma\Model\ResourceModel\OfflineItem\CollectionFactory $offlineitemCollectionFactory
    ) {
        $this->itemCollectionFactory        = $itemCollectionFactory;
        $this->offlineitemCollectionFactory = $offlineitemCollectionFactory;
    }

    /**
     * @param int $reasonId
     * @return int
     */
    public function get($reasonId)
    {
        $itemCollection = $this->itemCollectionFactory->create();
        $itemCollection->getSelect()->
            where('main_table.reason_id = '.$reasonId)
            ->columns('count(item_id) as reason_cnt')
        ;
        $itemsCnt = $itemCollection->getFirstItem()->getData('reason_cnt');

        $offlineitemCollection = $this->offlineitemCollectionFactory->create();
        $offlineitemCollection->getSelect()->
            where('main_table.reason_id = '.$reasonId)
            ->columns('count(offline_item_id) as reason_cnt')
        ;
        $itemsCnt += $offlineitemCollection->getFirstItem()->getData('reason_cnt');

        return $itemsCnt;
    }
}