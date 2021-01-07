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



namespace Mirasvit\Rma\Api\Data;

interface OfflineItemInterface extends DataInterface
{
    const KEY_OFFLINE_ITEM_ID = 'offline_item_id';
    const KEY_RMA_ID = 'rma_id';
    const KEY_OFFLINE_ORDER_ID = 'offline_order_id';
    const KEY_NAME = 'name';
    const KEY_REASON_ID = 'reason_id';
    const KEY_RESOLUTION_ID = 'resolution_id';
    const KEY_CONDITION_ID = 'condition_id';
    const KEY_QTY_REQUESTED = 'qty_requested';
    const KEY_CREATED_AT = 'created_at';
    const KEY_UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getOfflineItemId();

    /**
     * @param int $offlineItemId
     * @return $this
     */
    public function setOfflineItemId($offlineItemId);

    /**
     * @return int
     */
    public function getRmaId();

    /**
     * @param int $rmaId
     * @return $this
     */
    public function setRmaId($rmaId);

    /**
     * @return int
     */
    public function getOfflineOrderId();

    /**
     * @param int $offlineOrderId
     * @return $this
     */
    public function setOfflineOrderId($offlineOrderId);

    /**
     * @return int
     */
    public function getReasonId();

    /**
     * @param int $reasonId
     * @return $this
     */
    public function setReasonId($reasonId);

    /**
     * @return int
     */
    public function getResolutionId();

    /**
     * @param int $resolutionId
     * @return $this
     */
    public function setResolutionId($resolutionId);

    /**
     * @return int
     */
    public function getConditionId();

    /**
     * @param int $conditionId
     * @return $this
     */
    public function setConditionId($conditionId);

    /**
     * @return int
     */
    public function getQtyRequested();

    /**
     * @param int $qtyRequested
     * @return $this
     */
    public function setQtyRequested($qtyRequested);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);
}