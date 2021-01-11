<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Appadmin\Model\Staff;

use Exception;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magestore\Appadmin\Api\Data\Staff\StaffInterface;
use Magestore\Appadmin\Api\Event\DispatchServiceInterface;
use Magestore\Appadmin\Api\Staff\RuleRepositoryInterface;
use Magestore\Appadmin\Api\Staff\StaffRepositoryInterface;
use Magestore\Appadmin\Model\ResourceModel\Staff\Staff\Collection;
use Magestore\Appadmin\Model\ResourceModel\Staff\Staff\CollectionFactory;
use Magestore\Appadmin\Model\Source\Adminhtml\Status;
use Magestore\Webpos\Model\ResourceModel\Location\Location;

/**
 * Model Staff
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Staff extends AbstractModel implements StaffInterface
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    /**
     * @var CollectionFactory
     */
    protected $staffCollectionFactory;
    /**
     * @var \Magestore\Appadmin\Model\ResourceModel\Staff\Staff
     */
    protected $staffResource;
    /**
     * @var DispatchServiceInterface
     */
    protected $dispatchService;
    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;
    /**
     * @var StaffRepositoryInterface
     */
    protected $staffRepository;
    /**
     * @var Location
     */
    protected $locationResource;

    protected $currentData = [];

    /**
     * Staff constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param EncryptorInterface $encryptor
     * @param \Magestore\Appadmin\Model\ResourceModel\Staff\Staff $resource
     * @param Collection $resourceCollection
     * @param CollectionFactory $staffCollectionFactory
     * @param DispatchServiceInterface $dispatchService
     * @param RuleRepositoryInterface $ruleRepository
     * @param StaffRepositoryInterface $staffRepository
     * @param Location $locationResource
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EncryptorInterface $encryptor,
        \Magestore\Appadmin\Model\ResourceModel\Staff\Staff $resource,
        Collection $resourceCollection,
        CollectionFactory $staffCollectionFactory,
        DispatchServiceInterface $dispatchService,
        RuleRepositoryInterface $ruleRepository,
        StaffRepositoryInterface $staffRepository,
        Location $locationResource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->encryptor = $encryptor;
        $this->staffCollectionFactory = $staffCollectionFactory;
        $this->staffResource = $resource;
        $this->dispatchService = $dispatchService;
        $this->ruleRepository = $ruleRepository;
        $this->staffRepository = $staffRepository;
        $this->locationResource = $locationResource;
    }

    /**
     * Get Encoded Password
     *
     * @param string $password
     * @return string
     */
    public function getEncodedPassword($password)
    {
        return $this->encryptor->getHash($password, true);
    }

    /**
     * Before Save
     *
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $data = [];
        if ($this->getNewPassword()) {
            $data['password'] = $this->getEncodedPassword($this->getNewPassword());

            // dispatch event force sign out when change password
            if (!$this->isObjectNew()) {
                $this->dispatchService->dispatchEventForceSignOut($this->getStaffId());
            }
        } elseif ($this->getPassword() && !$this->getId() && !$this->getNotEncode()) {
            $data['password'] = $this->getEncodedPassword($this->getPassword());
        }
        $this->addData($data);

        // set data object before save
        if (!$this->isObjectNew()) {
            $currentObject = $this->staffRepository->getById($this->getStaffId());
            $this->currentData = $currentObject->getData();
        }

        return parent::beforeSave();
    }

    /**
     * After Save
     *
     * @return Staff
     * @throws LocalizedException
     */
    public function afterSave()
    {
        if (!$this->isObjectNew()) {
            // dispatch event force sign out when not have permission
            // or disable staff
            if ((!$this->ruleRepository->isAllowPermission('Magestore_Webpos::manage_pos', $this->getStaffId())
                || $this->getStatus() == Status::STATUS_DISABLED)) {
                $this->dispatchService->dispatchEventForceSignOut($this->getStaffId());
            }

            // dispatch event force change pos when remove some location assigned
            if ($this->hasDataChangeForField('location_ids')) {
                // get list location id which has been deleted
                $oldLocationIds = explode(',', $this->currentData['location_ids']);
                $newLocationIds = explode(',', $this->getData('location_ids'));
                $locationDeleted = [];
                foreach ($oldLocationIds as $locationId) {
                    if (!in_array($locationId, $newLocationIds)) {
                        $locationDeleted[] = $locationId;
                    }
                }

                $this->forceChangePosForStaff($locationDeleted);
            }
        }
        return parent::afterSave();
    }

    /**
     * Force Change Pos For Staff
     *
     * @param array $locationDeleted
     * @throws LocalizedException
     */
    public function forceChangePosForStaff($locationDeleted)
    {
        foreach ($locationDeleted as $locationId) {
            /** @var Location $resourceLocation */
            $resourceLocation = $this->locationResource;
            $select = $resourceLocation->getConnection()->select();
            $select->from(['e' => $resourceLocation->getMainTable()]);
            $select->join(
                ['pos' => $resourceLocation->getTable('webpos_pos')],
                'pos.location_id = e.' . $resourceLocation->getIdFieldName(),
                []
            );
            $select->where('staff_id = ' . $this->getStaffId());
            $select->where('pos.location_id = ' . $locationId);
            $select->columns('pos.pos_id');

            $data = $resourceLocation->getConnection()->fetchAll($select);
            foreach ($data as $datum) {
                // dispatch event force change pos
                $this->dispatchService->dispatchEventForceChangePos($this->getStaffId(), $datum['pos_id']);
            }
        }
    }

    /**
     * Before Delete
     *
     * @return Staff
     * @throws LocalizedException
     */
    public function beforeDelete()
    {
        $this->dispatchService->dispatchEventForceSignOut($this->getStaffId());
        return parent::beforeDelete();
    }

    /**
     * Check if data has change for field
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasDataChangeForField($fieldName)
    {
        return !($this->currentData[$fieldName] == $this->getData($fieldName));
    }

    /**
     * LoadByUsername
     *
     * @param string $username
     * @return $this
     */
    public function loadByUsername($username)
    {
        $staffCollection = $this->staffCollectionFactory->create()->addFieldToFilter(self::USER_NAME, $username)
            ->addFieldToFilter(self::STATUS, self::STATUS_ENABLED);
        if ($id = $staffCollection->getFirstItem()->getId()) {
            $this->staffResource->load($this, $id);
        }
        return $this;
    }

    /**
     * Authenticate
     *
     * @param string $username
     * @param string $password
     * @return $this|bool
     * @throws Exception
     */
    public function authenticate($username, $password)
    {
        $this->loadByUsername($username);
        if (!$this->validatePassword($password)) {
            return false;
        }
        return $this;
    }

    /**
     * Function ValidatePasswordUtils used for check length, same password
     *
     * @param string $newPassword
     * @param string $confirmationPassword
     * @return array
     */
    private function validatePasswordUtils($newPassword, $confirmationPassword)
    {
        $errors = [];
        if ($newPassword) {
            if (strlen($newPassword) < self::MIN_PASSWORD_LENGTH) {
                $errors[] = __('Password must be at least %1 characters.', self::MIN_PASSWORD_LENGTH);
            }

            if (!preg_match('/[a-z]/iu', $newPassword)
                || !preg_match('/[0-9]/u', $newPassword)) {
                $errors[] = __('Password must include both numeric and alphabetic characters.');
            }

            if ($confirmationPassword && $newPassword != $confirmationPassword) {
                $errors[] = __('Password confirmation must be same as password.');
            }
        }

        return $errors;
    }

    /**
     * Validate
     *
     * @return array|bool
     */
    public function validate()
    {
        $errors = $this->validatePasswordUtils($this->getNewPassword(), $this->getPasswordConfirmation());
        if ($this->userExists()) {
            $errors[] = __('A user with the same user name already exists.');
        }
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Validate Change Password
     *
     * @param string $newPassword
     * @param string $confirmationPassword
     * @return array|bool
     */
    public function validateChangePass($newPassword, $confirmationPassword)
    {
        $errors = $this->validatePasswordUtils($newPassword, $confirmationPassword);
        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

    /**
     * Validate Password
     *
     * @param string $password
     * @return bool
     * @throws Exception
     */
    public function validatePassword($password)
    {
        $hash = $this->getPassword();
        if (!$hash) {
            return false;
        }
        return $this->encryptor->validateHash($password, $hash);
    }

    /**
     * Get staff id
     *
     * @return string|null
     * @api
     */
    public function getStaffId()
    {
        return $this->getData(self::STAFF_ID);
    }

    /**
     * Set staff id
     *
     * @param string $staffId
     * @return $this
     * @api
     */
    public function setStaffId($staffId)
    {
        $this->setData(self::STAFF_ID, $staffId);
        return $this;
    }

    /**
     * Get user name
     *
     * @return string|null
     * @api
     */
    public function getUsername()
    {
        return $this->getData(self::USER_NAME);
    }

    /**
     * Set user name
     *
     * @param string $username
     * @return $this
     * @api
     */
    public function setUsername($username)
    {
        $this->setData(self::USER_NAME, $username);
        return $this;
    }

    /**
     * Get password params
     *
     * @return string|null
     * @api
     */
    public function getPassword()
    {
        return $this->getData(self::PASSWORD);
    }

    /**
     * Set password
     *
     * @param string $password
     * @return $this
     * @api
     */
    public function setPassword($password)
    {
        $this->setData(self::PASSWORD, $password);
        return $this;
    }

    /**
     * Get display name
     *
     * @return string|null
     * @api
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set display name
     *
     * @param string $name
     * @return $this
     * @api
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
        return $this;
    }

    /**
     * Get email
     *
     * @return string|null
     * @api
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set display name
     *
     * @param string $email
     * @return $this
     * @api
     */
    public function setEmail($email)
    {
        $this->setData(self::EMAIL, $email);
        return $this;
    }

    /**
     * Get customer group
     *
     * @return string|null
     * @api
     */
    public function getCustomerGroups()
    {
        return $this->getData(self::CUSTOMER_GROUPS);
    }

    /**
     * Set customer group
     *
     * @param string $customerGroups
     * @return $this
     * @api
     */
    public function setCustomerGroups($customerGroups)
    {
        $this->setData(self::CUSTOMER_GROUPS, $customerGroups);
        return $this;
    }

    /**
     * Get location ids
     *
     * @return string|null
     * @api
     */
    public function getLocationIds()
    {
        return $this->getData(self::LOCATION_IDS);
    }

    /**
     * Set location ids
     *
     * @param string $locationIds
     * @return $this
     * @api
     */
    public function setLocationIds($locationIds)
    {
        $this->setData(self::LOCATION_IDS, $locationIds);
        return $this;
    }

    /**
     * Get role id
     *
     * @return string|null
     * @api
     */
    public function getRoleId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * Set role id
     *
     * @param string $roleId
     * @return $this
     * @api
     */
    public function setRoleId($roleId)
    {
        $this->setData(self::ROLE_ID, $roleId);
        return $this;
    }

    /**
     * Get status
     *
     * @return string|null
     * @api
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     * @api
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    /**
     * Get pos id
     *
     * @return string|null
     * @api
     */
    public function getPosIds()
    {
        return $this->getData(self::POS_IDS);
    }

    /**
     * Set pos id
     *
     * @param string $posIds
     * @return $this
     * @api
     */
    public function setPosIds($posIds)
    {
        $this->setData(self::POS_IDS, $posIds);
        return $this;
    }

    /**
     * User Exists
     *
     * @return bool
     */
    public function userExists()
    {
        $username = $this->getUsername();
        $check = $this->staffCollectionFactory->create()->addFieldToFilter(self::USER_NAME, $username);
        if ($check->getFirstItem()->getId() && $this->getId() != $check->getFirstItem()->getId()) {
            return true;
        }
        return false;
    }
}
