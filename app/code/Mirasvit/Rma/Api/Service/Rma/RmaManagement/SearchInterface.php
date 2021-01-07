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



namespace Mirasvit\Rma\Api\Service\Rma\RmaManagement;

use Mirasvit\Rma\Api\Data\RmaInterface;

interface SearchInterface
{

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getItems(RmaInterface $rma);

    /**
     * @param int $orderItemId
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRmaItemsByOrderItem($orderItemId);

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\ItemInterface[]
     */
    public function getRequestedItems(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\OfflineItemInterface[]
     */
    public function getRequestedOfflineItems(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return array
     */
    public function getCustomerRead(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return array
     */
    public function getUserRead(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return array
     */
    public function getCustomerUnread(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return array
     */
    public function getUserUnread(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\MessageInterface[]
     */
    public function getMessages(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @return \Mirasvit\Rma\Api\Data\MessageInterface
     */
    public function getLastMessage(RmaInterface $rma);

    /**
     * @param RmaInterface $rma
     * @param int          $reasonId
     * @return int
     */
    public function hasRmaReason(RmaInterface $rma, $reasonId);

    /**
     * @param RmaInterface $rma
     * @param int          $conditionId
     * @return int
     */
    public function hasRmaCondition(RmaInterface $rma, $conditionId);

    /**
     * @param RmaInterface $rma
     * @param int          $resolutionId
     * @return int
     */
    public function hasRmaResolution(RmaInterface $rma, $resolutionId);
}