<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Customer\Data;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * Magestore webpos customer class
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Customer extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magestore\Webpos\Api\Data\Customer\CustomerInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadataService;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $attributeValueFactory
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadataService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $attributeValueFactory,
        \Magento\Customer\Api\CustomerMetadataInterface $metadataService,
        $data = []
    ) {
        $this->metadataService = $metadataService;
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function getCustomAttributesCodes()
    {
        if ($this->customAttributesCodes === null) {
            $this->customAttributesCodes = $this->getEavAttributesCodes($this->metadataService);
        }
        return $this->customAttributesCodes;
    }

    /**
     * GetDefaultBilling
     *
     * @return string|null
     */
    public function getDefaultBilling()
    {
        return $this->_get(self::DEFAULT_BILLING);
    }

    /**
     * Get default shipping address id
     *
     * @return string|null
     */
    public function getDefaultShipping()
    {
        return $this->_get(self::DEFAULT_SHIPPING);
    }

    /**
     * Get confirmation
     *
     * @return string|null
     */
    public function getConfirmation()
    {
        return $this->_get(self::CONFIRMATION);
    }

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Get created in area
     *
     * @return string|null
     */
    public function getCreatedIn()
    {
        return $this->_get(self::CREATED_IN);
    }

    /**
     * Get updated at time
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Get date of birth
     *
     * @return string|null
     */
    public function getDob()
    {
        return $this->_get(self::DOB);
    }

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_get(self::EMAIL);
    }

    /**
     * Get first name
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->_get(self::FIRSTNAME);
    }

    /**
     * Get gender
     *
     * @return string|null
     */
    public function getGender()
    {
        return $this->_get(self::GENDER);
    }

    /**
     * Get group id
     *
     * @return string|null
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->_get(self::LASTNAME);
    }

    /**
     * Get middle name
     *
     * @return string|null
     */
    public function getMiddlename()
    {
        return $this->_get(self::MIDDLENAME);
    }

    /**
     * Get prefix
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->_get(self::PREFIX);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Get suffix
     *
     * @return string|null
     */
    public function getSuffix()
    {
        return $this->_get(self::SUFFIX);
    }

    /**
     * Get tax Vat.
     *
     * @return string|null
     */
    public function getTaxvat()
    {
        return $this->_get(self::TAXVAT);
    }

    /**
     * Get website id
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }

    /**
     * Get addresses
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]|null
     */
    public function getAddresses()
    {
        return $this->_get(self::KEY_ADDRESSES);
    }

    /**
     * Get disable auto group change flag.
     *
     * @return int|null
     */
    public function getDisableAutoGroupChange()
    {
        return $this->_get(self::DISABLE_AUTO_GROUP_CHANGE);
    }

    /**
     * Set customer id
     *
     * @param int $id
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set group id
     *
     * @param int $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set default billing address id
     *
     * @param string $defaultBilling
     * @return $this
     */
    public function setDefaultBilling($defaultBilling)
    {
        return $this->setData(self::DEFAULT_BILLING, $defaultBilling);
    }

    /**
     * Set default shipping address id
     *
     * @param string $defaultShipping
     * @return $this
     */
    public function setDefaultShipping($defaultShipping)
    {
        return $this->setData(self::DEFAULT_SHIPPING, $defaultShipping);
    }

    /**
     * Set confirmation
     *
     * @param string $confirmation
     * @return $this
     */
    public function setConfirmation($confirmation)
    {
        return $this->setData(self::CONFIRMATION, $confirmation);
    }

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Set created in area
     *
     * @param string $createdIn
     * @return $this
     */
    public function setCreatedIn($createdIn)
    {
        return $this->setData(self::CREATED_IN, $createdIn);
    }

    /**
     * Set date of birth
     *
     * @param string $dob
     * @return $this
     */
    public function setDob($dob)
    {
        return $this->setData(self::DOB, $dob);
    }

    /**
     * Set email address
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Set first name
     *
     * @param string $firstname
     * @return $this
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * Set last name
     *
     * @param string $lastname
     * @return string
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * Set middle name
     *
     * @param string $middlename
     * @return $this
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::MIDDLENAME, $middlename);
    }

    /**
     * Set prefix
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * Set suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::SUFFIX, $suffix);
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return $this
     */
    public function setGender($gender)
    {
        return $this->setData(self::GENDER, $gender);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Set tax Vat
     *
     * @param string $taxvat
     * @return $this
     */
    public function setTaxvat($taxvat)
    {
        return $this->setData(self::TAXVAT, $taxvat);
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Set customer addresses.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return $this
     */
    public function setAddresses(array $addresses = null)
    {
        return $this->setData(self::KEY_ADDRESSES, $addresses);
    }

    /**
     * Set disable auto group change flag.
     *
     * @param int $disableAutoGroupChange
     * @return $this
     */
    public function setDisableAutoGroupChange($disableAutoGroupChange)
    {
        return $this->setData(self::DISABLE_AUTO_GROUP_CHANGE, $disableAutoGroupChange);
    }
    /**
     * Get customer billing telephone
     *
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->_get(self::TELEPHONE);
    }

    /**
     * Set customer billing telephone
     *
     * @param string $telephone
     * @return $this
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Get subscriber
     *
     * @return int|null
     */
    public function getSubscriberStatus()
    {
        return $this->_get(self::SUBSCRIBER_STATUS);
    }

    /**
     * Set subscriber
     *
     * @param int $subscriberStatus
     * @return $this
     */
    public function setSubscriberStatus($subscriberStatus)
    {
        return $this->setData(self::SUBSCRIBER_STATUS, $subscriberStatus);
    }

    /**
     * Get full name
     *
     * @return string|null
     */
    public function getFullName()
    {
        return $this->_get(self::FULL_NAME);
    }

    /**
     * Set full name
     *
     * @param string $fullName
     * @return $this
     */
    public function setFullName($fullName)
    {
        return $this->setData(self::FULL_NAME, $fullName);
    }

    /**
     * Get search string
     *
     * @api
     * @return string|null
     */
    public function getSearchString()
    {
        $searchString = $this->_get(self::EMAIL)
            . ' ' . $this->getFirstname()
            . ' ' . $this->getLastname()
            . ' ' . $this->_get(self::TELEPHONE);
        return $searchString;
    }

    /**
     * Get credit balance
     *
     * @return float|null
     */
    public function getCreditBalance()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get(\Magestore\Webpos\Helper\Data::class);
        if ($helper->isStoreCreditEnable()) {
            $customerId = $this->getId();
            $customerCredit = $objectManager->get(\Magestore\Customercredit\Model\CustomercreditFactory::class)
                ->create();
            $credit = $customerCredit->load($customerId, 'customer_id');
            if ($credit->getId()) {
                $creditBalance = $credit->getCreditBalance();
                return $creditBalance;
            }
        }
        return 0;
    }

    /**
     * Set customer is creating
     *
     * @param string $isCreating
     * @return CustomerInterface
     */
    public function setIsCreating($isCreating)
    {
        return $this;
    }

    /**
     * Get customer is creating
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCreating()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTmpCustomerId()
    {
        return $this->_get(self::TMP_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTmpCustomerId($tmpCustomerId)
    {
        return $this->setData(self::TMP_CUSTOMER_ID, $tmpCustomerId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedLocationId()
    {
        return $this->_get(self::CREATED_LOCATION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedLocationId($createdLocationId)
    {
        return $this->setData(self::CREATED_LOCATION_ID, $createdLocationId);
    }
    /**
     * @inheritDoc
     *
     * @param \Magestore\Webpos\Api\Data\Customer\CustomerExtensionInterface $exAttrs
     * @return $this
     */
    public function setExtensionAttributes(\Magestore\Webpos\Api\Data\Customer\CustomerExtensionInterface $exAttrs)
    {
        return $this->_setExtensionAttributes($exAttrs);
    }
    /**
     * @inheritDoc
     *
     * @return \Magestore\Webpos\Api\Data\Customer\CustomerExtensionInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
}
