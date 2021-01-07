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

interface OfflineOrderInterface extends DataInterface
{
    const KEY_OFFLINE_ORDER_ID = 'offline_order_id';
    const KEY_RECEIPT_NUMBER   = 'receipt_number';
    const KEY_CUSTOMER_ID      = 'customer_id';
    const KEY_STORE_ID         = 'store_id';

    /**
     * @return bool
     */
    public function getIsOffline();

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
     * @return string
     */
    public function getReceiptNumber();

    /**
     * @param string $receiptNumber
     * @return $this
     */
    public function setReceiptNumber($receiptNumber);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);
}
