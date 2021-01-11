<?php

/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Webpos\Api\Staff;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magestore\Appadmin\Api\Data\Staff\StaffInterface;
use Magestore\Webpos\Api\Data\Staff\Login\LoginResultInterface;
use Magestore\Webpos\Api\Data\Staff\Logout\LogoutResultInterface;

interface StaffManagementInterface
{
    /**
     * Function login api
     *
     * @param StaffInterface $staff
     * @return LoginResultInterface
     * @throws AuthorizationException
     * @throws LocalizedException
     */
    public function login($staff);

    /**
     * Function continueLogin api
     *
     * @return LoginResultInterface
     */
    public function continueLogin();

    /**
     * Function Logout
     *
     * @return LogoutResultInterface
     */
    public function logout();

    /**
     * Function isAllowResource
     *
     * @param string $resource
     * @param int $staffId
     * @return bool
     */
    public function isAllowResource($resource, $staffId);

    /**
     * Function authorizeSession
     *
     * @param string $phpSession
     * @return int
     * @throws LocalizedException
     */
    public function authorizeSession($phpSession);

    /**
     * Change password api
     *
     * @param string $username
     * @param string $oldPassword
     * @param string $newPassword
     * @param string $confirmationPassword
     * @return Json
     * @throws CouldNotSaveException
     */
    public function changePassword($username, $oldPassword, $newPassword, $confirmationPassword);
}
