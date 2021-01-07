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
 * @method Api\Data\RmaSearchResultsInterface getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
 */
interface RmaInterface extends DataInterface
{
    const TABLE_NAME  = 'mst_rma_rma';

    const KEY_INCREMENT_ID       = 'increment_id';
    const KEY_GUEST_ID           = 'guest_id';
    const KEY_FIRSTNAME          = 'firstname';
    const KEY_LASTNAME           = 'lastname';
    const KEY_COMPANY            = 'company';
    const KEY_TELEPHONE          = 'telephone';
    const KEY_EMAIL              = 'email';
    const KEY_STREET             = 'street';
    const KEY_CITY               = 'city';
    const KEY_REGION             = 'region';
    const KEY_REGION_ID          = 'region_id';
    const KEY_COUNTRY_ID         = 'country_id';
    const KEY_POSTCODE           = 'postcode_id';
    const KEY_CUSTOMER_ID        = 'customer_id';
    const KEY_ORDER_ID           = 'order_id';
    const KEY_STATUS_ID          = 'status_id';
    const KEY_STORE_ID           = 'store_id';
    const KEY_IS_RESOLVED        = 'is_resolved';
    const KEY_CREATED_AT         = 'created_at';
    const KEY_UPDATED_AT         = 'updated_at';
    const KEY_IS_GIFT            = 'is_gift';
    const KEY_IS_ADMIN_READ      = 'is_admin_read';
    const KEY_USER_ID            = 'user_id';
    const KEY_LAST_REPLY_NAME    = 'last_reply_name';
    const KEY_TICKET_ID          = 'ticket_id';
    const KEY_EXCHANGE_ORDER_IDS    = 'exchange_order_ids';
    const KEY_REPLACEMENT_ORDER_IDS = 'replacement_order_ids';
    const KEY_CREDIT_MEMO_IDS       = 'credit_memo_ids';
    const KEY_RETURN_ADDRESS        = 'return_address';
    const KEY_STATUS_HISTORY        = 'status_history';

    const MESSAGE_CODE = 'RMA-';

    /**
     * @return string
     */
    public function getIncrementId();

    /**
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * @return string
     */
    public function getGuestId();

    /**
     * @param string $guestId
     * @return $this
     */
    public function setGuestId($guestId);

    /**
     * @return string
     */
    public function getFirstname();

    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname);

    /**
     * @return string
     */
    public function getLastname();

    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname($lastname);

    /**
     * @return string
     */
    public function getCompany();

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany($company);

    /**
     * @return string
     */
    public function getTelephone();

    /**
     * @param string $telepone
     * @return $this
     */
    public function setTelephone($telepone);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * @return string
     */
    public function getStreet();

    /**
     * @param string $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * @return string
     */
    public function getCity();

    /**
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getRegion();

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region);

    /**
     * @return int
     */
    public function getRegionId();

    /**
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId);

    /**
     * @return int
     */
    public function getCountryId();

    /**
     * @param int $countryId
     * @return $this
     */
    public function setCountryId($countryId);

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode);

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
    public function getStatusId();

    /**
     * @param int $statusId
     * @return $this
     */
    public function setStatusId($statusId);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * @return boolean|int
     */
    public function getIsResolved();

    /**
     * @param boolean $isResolved
     * @return $this
     */
    public function setIsResolved($isResolved);


    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $date
     * @return $this
     */
    public function setCreatedAt($date);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $date
     * @return $this
     */
    public function setUpdatedAt($date);

    /**
     * @return bool|null
     */
    public function getIsGift();

    /**
     * @param bool $isGift
     * @return $this
     */
    public function setIsGift($isGift);

    /**
     * @return bool|null
     */
    public function getIsAdminRead();

    /**
     * @param bool $isAdminRead
     * @return $this
     */
    public function setIsAdminRead($isAdminRead);

    /**
     * @return int
     */
    public function getUserId();

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);

    /**
     * @return string
     */
    public function getLastReplyName();

    /**
     * @param string $lastReplyName
     * @return $this
     */
    public function setLastReplyName($lastReplyName);

    /**
     * @return int
     */
    public function getTicketId();

    /**
     * @param int $ticketId
     * @return $this
     */
    public function setTicketId($ticketId);

    /**
     * @return int[]
     */
    public function getExchangeOrderIds();

    /**
     * @param int[] $orderIds
     * @return $this
     */
    public function setExchangeOrderIds($orderIds);

    /**
     * @return int[]
     */
    public function getReplacementOrderIds();

    /**
     * @param int[] $orderIds
     * @return $this
     */
    public function setReplacementOrderIds($orderIds);

    /**
     * @return int[]
     */
    public function getCreditMemoIds();

    /**
     * @param int[] $creditMemoIds
     * @return $this
     */
    public function setCreditMemoIds($creditMemoIds);

    /**
     * @return string
     */
    public function getReturnAddress();

    /**
     * @param string $address
     * @return $this
     */
    public function setReturnAddress($address);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getStatusHistory();

    /**
     * @param string $statuses
     * @return $this
     */
    public function setStatusHistory($statuses);
}