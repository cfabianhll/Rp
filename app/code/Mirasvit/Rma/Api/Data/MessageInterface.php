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
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @method Api\Data\MessageSearchResultsInterface getList(SearchCriteriaInterface $searchCriteria)
 */
interface MessageInterface extends DataInterface
{
    const COMMENT_PUBLIC = 'public';
    const COMMENT_INTERNAL = 'internal';

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
    public function getUserId();

    /**
     * @param int $userId
     * @return $this
     */
    public function setUserId($userId);

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
     * @return string
     */
    public function getCustomerName();

    /**
     * @param string $customerName
     * @return $this
     */
    public function setCustomerName($customerName);

    /**
     * @return string
     */
    public function getText();

    /**
     * @param string $text
     * @return $this
     */
    public function setText($text);

    /**
     * @return bool|null
     */
    public function getIsHtml();

    /**
     * @param bool $isHtml
     * @return $this
     */
    public function setIsHtml($isHtml);

    /**
     * @return bool|null
     */
    public function getIsVisibleInFrontend();

    /**
     * @param bool $isVisibleInFrontend
     * @return $this
     */
    public function setIsVisibleInFrontend($isVisibleInFrontend);

    /**
     * @return bool|null
     */
    public function getIsCustomerNotified();

    /**
     * @param bool $isCustomerNotified
     * @return $this
     */
    public function setIsCustomerNotified($isCustomerNotified);

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
     * @return int
     */
    public function getEmailId();

    /**
     * @param int $emailId
     * @return $this
     */
    public function setEmailId($emailId);

    /**
     * @return bool|null
     */
    public function getIsRead();

    /**
     * @param bool $isRead
     * @return $this
     */
    public function setIsRead($isRead);
}