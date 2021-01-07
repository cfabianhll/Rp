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

/**
 * @method Api\Data\FieldSearchResultsInterface getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
 */
interface FieldInterface extends DataInterface
{
    const TABLE_NAME  = 'mst_rma_field';

    const ID = 'field_id';

    const KEY_NAME                        = 'name';
    const KEY_CODE                        = 'code';
    const KEY_TYPE                        = 'type';
    const KEY_ACCESSORY                   = 'accessory';
    const KEY_VALUES                      = 'values';
    const KEY_DESCRIPTION                 = 'description';
    const KEY_IS_ACTIVE                   = 'is_active';
    const KEY_SORT_ORDER                  = 'sort_order';
    const KEY_IS_REQUIRED_STAFF           = 'is_required_staff';
    const KEY_IS_REQUIRED_CUSTOMER        = 'is_required_customer';
    const KEY_IS_VISIBLE_CUSTOMER         = 'is_visible_customer';
    const KEY_IS_EDITABLE_CUSTOMER        = 'is_editable_customer';
    const KEY_VISIBLE_CUSTOMER_STATUS     = 'visible_customer_status';
    const KEY_IS_SHOW_IN_CONFIRM_SHIPPING = 'is_show_in_confirm_shipping';

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
     * @return string
     */
    public function getCode();

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getAccessory();

    /**
     * @param string $accessory
     * @return $this
     */
    public function setAccessory($accessory);

    /**
     * @return string|array
     */
    public function getValues();

    /**
     * @param string $values
     * @return $this
     */
    public function setValues($values);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return bool|int
     */
    public function getIsActive();

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

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
     * @return bool|int
     */
    public function getIsRequiredStaff();

    /**
     * @param bool $isRequiredStaff
     * @return $this
     */
    public function setIsRequiredStaff($isRequiredStaff);

    /**
     * @return bool|int
     */
    public function getIsRequiredCustomer();

    /**
     * @param bool $isRequiredCustomer
     * @return $this
     */
    public function setIsRequiredCustomer($isRequiredCustomer);

    /**
     * @return bool|int
     */
    public function getIsVisibleCustomer();

    /**
     * @param bool $isVisibleCustomer
     * @return $this
     */
    public function setIsVisibleCustomer($isVisibleCustomer);

    /**
     * @return bool|int
     */
    public function getIsEditableCustomer();

    /**
     * @param bool $isEditableCustomer
     * @return $this
     */
    public function setIsEditableCustomer($isEditableCustomer);

    /**
     * @return string
     */
    public function getVisibleCustomerStatus();

    /**
     * @param string $visibleCustomerStatus
     * @return $this
     */
    public function setVisibleCustomerStatus($visibleCustomerStatus);

    /**
     * @return bool|int
     */
    public function getIsShowInConfirmShipping();

    /**
     * @param bool $isShowInConfirmShipping
     * @return $this
     */
    public function setIsShowInConfirmShipping($isShowInConfirmShipping);
}