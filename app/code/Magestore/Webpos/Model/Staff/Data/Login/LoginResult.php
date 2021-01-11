<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Staff\Data\Login;

use Magento\Framework\Model\AbstractModel;
use Magestore\Webpos\Api\Data\Staff\Login\LocationResultInterface;
use Magestore\Webpos\Api\Data\Staff\Login\LoginResultExtensionInterface;
use Magestore\Webpos\Api\Data\Staff\Login\LoginResultInterface;

/**
 * Class LoginResult
 *
 * Class LoginResult is result of api login
 */
class LoginResult extends AbstractModel implements LoginResultInterface
{
    /**
     * Get staff id
     *
     * @api
     * @return int
     */
    public function getStaffId()
    {
        return $this->getData(self::STAFF_ID);
    }

    /**
     * Set staff id
     *
     * @api
     * @param int $staffId
     * @return $this
     */
    public function setStaffId($staffId)
    {
        return $this->setData(self::STAFF_ID, $staffId);
    }
    /**
     * Get session time out
     *
     * @api
     * @return int
     */
    public function getTimeout()
    {
        return $this->getData(self::TIME_OUT);
    }

    /**
     * Set session time out
     *
     * @api
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        return $this->setData(self::TIME_OUT, $timeout);
    }
    /**
     * Get session id
     *
     * @api
     * @return string
     */
    public function getSessionId()
    {
        return $this->getData(self::SESSION_ID);
    }

    /**
     * Set session id
     *
     * @api
     * @param string $sessionId
     * @return $this
     */
    public function setSessionId($sessionId)
    {
        return $this->setData(self::SESSION_ID, $sessionId);
    }
    /**
     * Get message
     *
     * @api
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set message
     *
     * @api
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }
    /**
     * Get staff name
     *
     * @api
     * @return string
     */
    public function getStaffName()
    {
        return $this->getData(self::STAFF_NAME);
    }

    /**
     * Set staff name
     *
     * @api
     * @param string $staffName
     * @return $this
     */
    public function setStaffName($staffName)
    {
        return $this->setData(self::STAFF_NAME, $staffName);
    }

    /**
     * Get locations
     *
     * @api
     * @return LocationResultInterface[]
     */
    public function getLocations()
    {
        return $this->getData(self::LOCATIONS);
    }

    /**
     * Set locations
     *
     * @api
     * @param LocationResultInterface[] $locations
     * @return $this
     */
    public function setLocations($locations)
    {
        return $this->setData(self::LOCATIONS, $locations);
    }

    /**
     * Get website id
     *
     * @api
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * Set staff id
     *
     * @api
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Function getSharingAccount
     *
     * @api
     * @return int
     */
    public function getSharingAccount()
    {
        return $this->getData(self::SHARING_ACCOUNT);
    }

    /**
     * Function setSharingAccount
     *
     * @api
     * @param int $sharingAccount
     * @return $this
     */
    public function setSharingAccount($sharingAccount)
    {
        return $this->setData(self::SHARING_ACCOUNT, $sharingAccount);
    }

    /**
     * Set token
     *
     * @api
     * @param string $token
     * @return LoginResultInterface
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get token
     *
     * @api
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        LoginResultExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * @inheritDoc
     */
    public function getStaffEmail()
    {
        return $this->getData(self::STAFF_EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setStaffEmail($staffEmail)
    {
        return $this->setData(self::STAFF_EMAIL, $staffEmail);
    }
}
