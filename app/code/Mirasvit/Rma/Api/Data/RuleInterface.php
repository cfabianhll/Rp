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
 * @method Api\Data\RuleSearchResultsInterface getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
 */
interface RuleInterface extends DataInterface
{
    const TABLE_NAME  = 'mst_rma_rule';

    const KEY_NAME                  = 'name';
    const KEY_EVENT                 = 'event';
    const KEY_EMAIL_SUBJECT         = 'email_subject';
    const KEY_EMAIL_BODY            = 'email_body';
    const KEY_IS_ACTIVE             = 'is_active';
    const KEY_CONDITIONS_SERIALIZED = 'conditions_serialized';
    const KEY_IS_SEND_OWNER         = 'is_send_owner';
    const KEY_IS_SEND_DEPARTMENT    = 'is_send_department';
    const KEY_IS_SEND_USER          = 'is_send_user';
    const KEY_OTHER_EMAIL           = 'other_email';
    const KEY_SORT_ORDER            = 'sort_order';
    const KEY_IS_STOP_PROCESSING    = 'is_stop_processing';
    const KEY_STATUS_ID             = 'status_id';
    const KEY_USER_ID               = 'user_id';
    const KEY_IS_SEND_ATTACHMENT    = 'is_send_attachment';
    const KEY_IS_RESOLVED           = 'is_resolved';

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
    public function getEvent();

    /**
     * @param string $event
     * @return $this
     */
    public function setEvent($event);

    /**
     * @return string
     */
    public function getEmailBody();

    /**
     * @param string $emailBody
     * @return $this
     */
    public function setEmailBody($emailBody);

    /**
     * @return string
     */
    public function getEmailSubject();

    /**
     * @param string $emailSubject
     * @return $this
     */
    public function setEmailSubject($emailSubject);

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
     * @return string
     */
    public function getConditionsSerialized();

    /**
     * @param string $conditionsSerialized
     * @return $this
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * @return bool|int
     */
    public function getIsSendOwner();

    /**
     * @param bool $isSendOwner
     * @return $this
     */
    public function setIsSendOwner($isSendOwner);

    /**
     * @return bool|int
     */
    public function getIsSendDepartment();

    /**
     * @param bool $isSendDepartment
     * @return $this
     */
    public function setIsSendDepartment($isSendDepartment);

    /**
     * @return bool|int
     */
    public function getIsSendUser();

    /**
     * @param bool $isSendUser
     * @return $this
     */
    public function setIsSendUser($isSendUser);

    /**
     * @return bool|int
     */
    public function getOtherEmail();

    /**
     * @param string $otherEmail
     * @return $this
     */
    public function setOtherEmail($otherEmail);

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
     * @return int
     */
    public function getIsStopProcessing();

    /**
     * @param int $isStopProcessing
     * @return $this
     */
    public function setIsStopProcessing($isStopProcessing);

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
    public function getUserId();

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);

    /**
     * @return bool
     */
    public function getIsSendAttachment();

    /**
     * @param bool $isSendAttachment
     * @return $this
     */
    public function setIsSendAttachment($isSendAttachment);

    /**
     * @return bool
     */
    public function getIsResolved();

    /**
     * @param bool $isResolved
     * @return $this
     */
    public function setIsResolved($isResolved);
}