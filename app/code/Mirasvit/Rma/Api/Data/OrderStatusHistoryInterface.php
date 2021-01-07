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

use Mirasvit\Rma\Api;

interface OrderStatusHistoryInterface extends DataInterface
{
    const KEY_HISTORY_ID = 'history_id';
    const KEY_ORDER_ID   = 'order_id';
    const KEY_STATUS     = 'status';
    const KEY_CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getHistoryId();

    /**
     * @param int $historyId
     * @return $this
     */
    public function setHistoryId($historyId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date);
}