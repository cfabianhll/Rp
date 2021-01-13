<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Data\Customer;

/**
 * Magestore's Customer interface.
 */
interface CustomerInterface extends \Magento\Customer\Api\Data\CustomerInterface
{
    const TELEPHONE = 'telephone';
    const SUBSCRIBER_STATUS = 'subscriber_status';
    const FULL_NAME = 'full_name';
    const SEARCH_STRING = 'search_string';
    const CREDIT_BALANCE = 'credit_balance';
    const IS_CREATING = 'is_creating';
    const TMP_CUSTOMER_ID = 'tmp_customer_id';
    const CREATED_LOCATION_ID = "created_location_id";
    /**
     * Get customer telephone
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set customer telephone
     *
     * @param string $telephone
     * @return CustomerInterface
     */
    public function setTelephone($telephone);
    /**
     * Get subscriber
     *
     * @return int|null
     */
    public function getSubscriberStatus();

    /**
     * Set subscriber
     *
     * @api
     * @param int $subscriberStatus
     * @return CustomerInterface
     */
    public function setSubscriberStatus($subscriberStatus);

    /**
     * Get full name
     *
     * @return string|null
     */
    public function getFullName();

    /**
     * Set full name
     *
     * @param string $fullName
     * @return CustomerInterface
     */
    public function setFullName($fullName);
    /**
     * Get search string
     *
     * @return string|null
     */
    public function getSearchString();

    /**
     * Get credit balance
     *
     * @return float|null
     */
    public function getCreditBalance();

    /**
     * Set customer is creating
     *
     * @param string $isCreating
     * @return CustomerInterface
     */
    public function setIsCreating($isCreating);

    /**
     * Get customer is creating
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCreating();

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender();

    /**
     * Set gender
     *
     * @param string|null $gender
     * @return $this
     */
    public function setGender($gender);

    /**
     * Get temp customer id
     *
     * @return string|null
     */
    public function getTmpCustomerId();

    /**
     * Set temp customer id
     *
     * @param string|null $tmpCustomerId
     * @return $this
     */
    public function setTmpCustomerId($tmpCustomerId);

    /**
     * Get location Id from which customer is created
     *
     * @return int|null
     */
    public function getCreatedLocationId();

    /**
     * Set location Id from which customer is created
     *
     * @param int $createdLocationId
     * @return $this
     */
    public function setCreatedLocationId($createdLocationId);
	
	/**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Customer\Api\Data\CustomerExtensionInterface|\Magestore\Webpos\Api\Data\Customer\CustomerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes);
}
