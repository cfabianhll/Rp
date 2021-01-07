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

interface StatusInterface extends DataInterface
{
    const TABLE_NAME  = 'mst_rma_status';

    const APPROVED     = 'approved';
    const PACKAGE_SENT = 'package_sent';
    const REJECTED     = 'rejected';
    const CLOSED       = 'closed';

    const KEY_NAME             = 'name';
    const KEY_SORT_ORDER       = 'sort_order';
    const KEY_IS_SHOW_SHIPPING = 'is_show_shipping';
    const KEY_CUSTOMER_MESSAGE = 'customer_message';
    const KEY_ADMIN_MESSAGE    = 'admin_message';
    const KEY_HISTORY_MESSAGE  = 'history_message';
    const KEY_IS_ACTIVE        = 'is_active';
    const KEY_CODE             = 'code';
    const KEY_CHILDREN_IDS     = 'children_ids';
    const KEY_IS_VISIBLE       = 'is_visible';
    const KEY_IS_MAIN_BRANCH   = 'is_main_branch';
    const KEY_COLOR            = 'color';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * @return bool|null
     */
    public function getIsShowShipping();

    /**
     * @param bool $isShowShipping
     * @return $this
     */
    public function setIsShowShipping($isShowShipping);

    /**
     * @return string
     */
    public function getCustomerMessage();

    /**
     * @param string $customerMessage
     * @return $this
     */
    public function setCustomerMessage($customerMessage);

    /**
     * @return string
     */
    public function getAdminMessage();

    /**
     * @param string $adminMessage
     * @return $this
     */
    public function setAdminMessage($adminMessage);

    /**
     * @return string
     */
    public function getHistoryMessage();

    /**
     * @param string $historyMessage
     * @return $this
     */
    public function setHistoryMessage($historyMessage);

    /**
     * @return bool|null
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return int[]
     */
    public function getChildrenIds();

    /**
     * @param int[] $ids
     * @return $this
     */
    public function setChildrenIds($ids);

    /**
     * @return bool
     */
    public function getIsVisible();

    /**
     * @param bool $visible
     * @return $this
     */
    public function setIsVisible($visible);

    /**
     * @return bool
     */
    public function getIsMainBranch();

    /**
     * @param bool $mainBranch
     * @return $this
     */
    public function setIsMainBranch($mainBranch);

    /**
     * @return string
     */
    public function getColor();

    /**
     * @param string $color
     * @return $this
     */
    public function setColor($color);
}