<?php
/**
 * Copyright © 2018 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\Webpos\Model\Staff;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magestore\Appadmin\Api\Data\Staff\StaffInterface;
use Magestore\Appadmin\Api\Event\DispatchServiceInterface;
use Magestore\Appadmin\Api\Staff\StaffRepositoryInterface;
use Magestore\Appadmin\Model\ResourceModel\Staff\AuthorizationRule\CollectionFactory as RuleCollectionFactory;
use Magestore\Appadmin\Model\Staff\StaffFactory;
use Magestore\Webpos\Api\Data\Staff\Login\LoginResultInterface;
use Magestore\Webpos\Api\Data\Staff\Logout\LogoutResultInterface;
use Magestore\Webpos\Api\Data\Staff\SessionInterface;
use Magestore\Webpos\Api\Location\LocationRepositoryInterface;
use Magestore\Webpos\Api\Pos\PosRepositoryInterface;
use Magestore\Webpos\Api\Staff\SessionRepositoryInterface;
use Magestore\Webpos\Api\Staff\StaffManagementInterface;
use Magestore\WebposIntegration\Api\ApiServiceInterface;
use Magestore\WebposIntegration\Controller\Rest\RequestProcessor;
use Psr\Log\LoggerInterface;

/**
 * Model StaffManagement
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class StaffManagement implements StaffManagementInterface
{
    /**
     * Const is_sharing_account
     */
    const IS_SHARING_ACCOUNT = 1;
    /**
     * @var LoginResultInterface
     */
    protected $loginResult;
    /**
     * @var StaffRepositoryInterface
     */
    protected $staffRepository;
    /**
     * @var SessionManagerInterface
     */
    protected $sessionManagerInterface;
    /**
     * @var TimezoneInterface
     */
    protected $timezone;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var StaffFactory
     */
    protected $staffFactory;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var SessionRepositoryInterface
     */
    protected $sessionRepository;
    /**
     * @var LocationRepositoryInterface
     */
    protected $locationRepository;
    /**
     * @var PosRepositoryInterface
     */
    protected $posRepository;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LogoutResultInterface
     */
    protected $logoutResult;
    /**
     * @var AuthorizationRuleFactory
     */
    protected $authorizationRuleCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerment;

    /**
     * @var DispatchServiceInterface
     */
    protected $dispatchService;

    /**
     * @var ApiServiceInterface
     */
    protected $apiService;
    /**
     * @var Json
     */
    protected $resultJson;

    /**
     * StaffManagement constructor.
     *
     * @param LoginResultInterface $loginResult
     * @param LogoutResultInterface $logoutResult
     * @param StaffRepositoryInterface $staffRepository
     * @param SessionManagerInterface $sessionManager
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     * @param StaffFactory $staffFactory
     * @param SessionInterface $session
     * @param SessionRepositoryInterface $sessionRepository
     * @param LocationRepositoryInterface $locationRepository
     * @param PosRepositoryInterface $posRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param Registry $coreRegistry
     * @param RuleCollectionFactory $authorizationRuleCollectionFactory
     * @param StoreManagerInterface $storeManagerment
     * @param DispatchServiceInterface $dispatchService
     * @param ApiServiceInterface $apiService
     * @param Json $resultJson
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LoginResultInterface $loginResult,
        LogoutResultInterface $logoutResult,
        StaffRepositoryInterface $staffRepository,
        SessionManagerInterface $sessionManager,
        TimezoneInterface $timezone,
        LoggerInterface $logger,
        StaffFactory $staffFactory,
        SessionInterface $session,
        SessionRepositoryInterface $sessionRepository,
        LocationRepositoryInterface $locationRepository,
        PosRepositoryInterface $posRepository,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        Registry $coreRegistry,
        RuleCollectionFactory $authorizationRuleCollectionFactory,
        StoreManagerInterface $storeManagerment,
        DispatchServiceInterface $dispatchService,
        ApiServiceInterface $apiService,
        Json $resultJson
    ) {
        $this->loginResult = $loginResult;
        $this->logoutResult = $logoutResult;
        $this->staffRepository = $staffRepository;
        $this->sessionManagerInterface = $sessionManager;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->staffFactory = $staffFactory;
        $this->session = $session;
        $this->sessionRepository = $sessionRepository;
        $this->locationRepository = $locationRepository;
        $this->posRepository = $posRepository;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->coreRegistry = $coreRegistry;
        $this->authorizationRuleCollectionFactory = $authorizationRuleCollectionFactory;
        $this->storeManagerment = $storeManagerment;
        $this->dispatchService = $dispatchService;
        $this->apiService = $apiService;
        $this->resultJson = $resultJson;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function login($staff)
    {
        $token = $this->getAccessToken();
        $username = $staff->getUsername();
        $password = $staff->getPassword();
        if ($username && $password) {
            try {
                $resultLogin = $this->staffFactory->create()->authenticate($username, $password);
                if ($resultLogin
                    && $resultLogin->getStatus() == StaffInterface::STATUS_ENABLED
                    && $resultLogin->getRoleId()) {
                    $this->sessionManagerInterface->regenerateId();
                    $sessionId = $this->sessionManagerInterface->getSessionId();
                    $this->session->setStaffId($resultLogin->getId());
                    $this->session->setSessionId($sessionId);
                    $this->session->setLoggedDate(
                        strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp())
                    );
                    $this->sessionRepository->save($this->session);
                    $roleId = $resultLogin->getRoleId();
                    $authenticationRule = $this->authorizationRuleCollectionFactory->create()
                        ->addFieldToFilter('role_id', $roleId)
                        ->addFieldToFilter(
                            'resource_id',
                            ['in' => ['Magestore_Appadmin::all', 'Magestore_Webpos::manage_pos']]
                        );
                    if (!$authenticationRule->getSize()) {
                        throw new AuthorizationException(
                            __('Your account is invalid. Please try again')
                        );
                    }
                    $this->loginResult->setToken($token);
                    $this->loginResult->setSessionId($sessionId);
                    $this->loginResult->setTimeout($this->scopeConfig->getValue('webpos/general/session_timeout'));
                    $this->loginResult->setStaffId($resultLogin->getId());
                    $this->loginResult->setStaffName($resultLogin->getName());
                    $this->loginResult->setStaffEmail($resultLogin->getEmail());
                    $this->loginResult->setMessage(__('Success'));
                    $this->loginResult->setWebsiteId($this->storeManagerment->getWebsite()->getId());

                    /* sharing pos config */
                    $expireTime = $this->getExpireTime();
                    $sharingAccountConfig = $this->scopeConfig->getValue('webpos/security/sharing_acount');
                    $listCurrentSessionWithPos = $this->sessionRepository->getListByStaffId($resultLogin->getId());
                    $listCurrentSessionWithPos->addFieldToFilter(
                        SessionInterface::POS_ID,
                        ['gt' => 0]
                    );
                    $listCurrentSessionWithPos->addFieldToFilter(
                        SessionInterface::LOGGED_DATE,
                        ['from' => $expireTime]
                    );
                    $hasOccupyPos = $listCurrentSessionWithPos->getSize();
                    $this->loginResult->setSharingAccount(self::IS_SHARING_ACCOUNT);
                    /* if sharing config = No and staff are processing on Pos */
                    if ($sharingAccountConfig != self::IS_SHARING_ACCOUNT && $hasOccupyPos > 0) {
                        $this->loginResult->setSharingAccount(!self::IS_SHARING_ACCOUNT);
                    }
                    /* if sharing account */
                    if ($this->loginResult->getSharingAccount() == self::IS_SHARING_ACCOUNT) {
                        // get list available location
                        $locations = $this->locationRepository->getListLocationWithStaff($resultLogin->getId());
                        if (!$locations) {
                            throw new LocalizedException(__('No POS available'));
                        }
                        $this->loginResult->setLocations($locations);
                    }
                    return $this->loginResult;
                } else {
                    throw new AuthorizationException(
                        __('Your account is invalid. Please try again'),
                        new Exception(),
                        DispatchServiceInterface::EXCEPTION_CODE_CANNOT_LOGIN
                    );
                }
            } catch (AuthorizationException $e) {
                throw new AuthorizationException(
                    __('Your account is invalid. Please try again'),
                    new Exception(),
                    DispatchServiceInterface::EXCEPTION_CODE_CANNOT_LOGIN
                );
            }
        }
        return $this->loginResult;
    }

    /**
     * @inheritdoc
     */
    public function continueLogin()
    {
        $token = $this->getAccessToken();
        $sessionId = $this->request->getParam(
            RequestProcessor::SESSION_PARAM_KEY
        );
        try {
            $sessionLogin = $this->sessionRepository->getBySessionId($sessionId);
            if ($sessionLogin) {
                $listCurrentSessionWithPos = $this->sessionRepository->getListByStaffId($sessionLogin->getStaffId());
                $listCurrentSessionWithPos->addFieldToFilter(
                    SessionInterface::SESSION_ID,
                    ['neq' => $sessionId]
                );
                /* delete list $session */
                if ($listCurrentSessionWithPos->getSize() > 0) {
                    foreach ($listCurrentSessionWithPos as $session) {
                        // delete current staff on POS  and force sign out.
                        if ($session->getPosId()) {
                            $pos = $this->posRepository->getById($session->getPosId());
                            $this->dispatchService->dispatchEventForceSignOut($pos->getStaffId(), $pos->getPosId());
                            $pos->setStaffId(null)->save();
                            $this->sessionRepository->signOutPos($pos->getPosId());
                        }
                    }
                }
                // get list available location
                $locations = $this->locationRepository->getListLocationWithStaff($sessionLogin->getStaffId());
                if (!$locations) {
                    throw new LocalizedException(__('No POS available'));
                }
                $this->loginResult->setToken($token);
                $this->loginResult->setSessionId($sessionId);
                $this->loginResult->setLocations($locations);
                $this->loginResult->setTimeout($this->scopeConfig->getValue('webpos/general/session_timeout'));
                $this->loginResult->setMessage(__('Success'));
                $this->loginResult->setSharingAccount(self::IS_SHARING_ACCOUNT);
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Cannot Continue to Login !'));
        }
        return $this->loginResult;
    }

    /**
     * Is Allow Resource
     *
     * @param string $resource
     * @param int $staffId
     * @return bool
     * @throws LocalizedException
     */
    public function isAllowResource($resource, $staffId)
    {
        $allPermission = $this->sessionRepository->getAllCurrentPermission($staffId);
        if (in_array('Magestore_Appadmin::all', $allPermission)) {
            return true;
        }
        if (in_array($resource, $allPermission)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Authorize Session
     *
     * @param string $phpSession
     * @return int
     * @throws LocalizedException
     */
    public function authorizeSession($phpSession)
    {
        $webposModel = $this->sessionRepository->getBySessionId($phpSession);
        if (!$this->coreRegistry->registry('currrent_webpos_staff')) {
            $staff = $this->staffRepository->getById($webposModel->getStaffId());
            $this->coreRegistry->register('currrent_webpos_staff', $staff);
        }

        if (!$this->coreRegistry->registry('currrent_session_model')) {
            $this->coreRegistry->register('currrent_session_model', $webposModel);
        }
        if ($webposModel->getId()) {
            $logTimeStaff = $webposModel->getData('logged_date');
            $currentTime = $this->timezone->scopeTimeStamp();
            $logTimeStamp = strtotime($logTimeStaff);
            if (($currentTime - $logTimeStamp) <= $this->scopeConfig->getValue('webpos/general/session_timeout')) {
                $newLoggedDate = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
                $webposModel->setData('logged_date', $newLoggedDate);
                $this->sessionRepository->save($webposModel);
                return $webposModel->getStaffId();
            } else {
                if ($webposModel->getPosId()) {
                    $this->posRepository->freePosById($webposModel->getPosId());
                }
                $this->sessionRepository->delete($webposModel);

                throw new AuthorizationException(
                    __('Opps! Access denied. Recent action has not been saved yet.'),
                    new Exception(),
                    DispatchServiceInterface::EXCEPTION_CODE_FORCE_SIGN_OUT
                );
            }
        } else {
            return 0;
        }
    }

    /**
     * @inheritdoc
     */
    public function logout()
    {
        $sessionId = $this->request->getParam(
            RequestProcessor::SESSION_PARAM_KEY
        );
        try {
            $sessionLogin = $this->sessionRepository->getBySessionId($sessionId);
            if ($sessionLogin) {
                /* get $expireTime */
                $expireTime = $this->getExpireTime();
                /**/
                $listCurrentSessionWithPos = $this->sessionRepository->getListByStaffId($sessionLogin->getStaffId());
                $listCurrentSessionWithPos->addFieldToFilter(
                    SessionInterface::POS_ID,
                    $sessionLogin->getPosId()
                );
                $listCurrentSessionWithPos->addFieldToFilter(
                    SessionInterface::LOGGED_DATE,
                    ['from' => $expireTime]
                );

                /** @var Session $sessionWithPos */
                foreach ($listCurrentSessionWithPos as $sessionWithPos) {
                    /* delete current staff on POS */
                    if ($sessionLogin->getPosId() === $sessionWithPos->getPosId()) {
                        $pos = $this->posRepository->getById($sessionLogin->getPosId());
                        $pos->setStaffId(null)->save();
                    }
                }

                $sessionLogin->delete($sessionLogin);
                $this->logoutResult->setMessage(__('Logout Success!'));
            }
        } catch (LocalizedException $e) {
            throw new LocalizedException(__('Cannot logout!'));
        }
        return $this->logoutResult;
    }

    /**
     * GetExpireTime
     *
     * @return false|string
     */
    public function getExpireTime()
    {
        $currentTimeStamp = $this->timezone->scopeTimeStamp();
        $expireTimeStamp = $currentTimeStamp - $this->scopeConfig->getValue('webpos/general/session_timeout');
        $expireTime = date('Y-m-d h:i:s', $expireTimeStamp);
        return $expireTime;
    }

    /**
     * Get Access Token
     *
     * @return string
     * @throws AuthorizationException
     */
    public function getAccessToken()
    {
        try {
            $token = $this->apiService->getToken();
        } catch (Exception $e) {
            throw new AuthorizationException(
                __("Can't retrieve access token, please get technical support.")
            );
        }
        return $token;
    }

    /**
     * Function customResult used for json return from changePassword function
     *
     * @param boolean $success
     * @param array $message
     * @return bool|Json|string
     */
    private function customResult($success, $message)
    {
        return $this->resultJson->serialize(
            [
                'success' => $success,
                'message' => $message
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function changePassword($username, $oldPassword, $newPassword, $confirmationPassword)
    {
        if ($username) {
            $resultStaff = $this->staffFactory->create()->loadByUsername($username);

            if ($resultStaff && $resultStaff->getId()) {
                $verifyOldPass = $resultStaff->validatePassword($oldPassword);
                // Current Password not correct
                if (!$verifyOldPass) {
                    return $this->customResult(false, ["Current password is not correct"]);
                } else {
                    $result = $resultStaff->validateChangePass($newPassword, $confirmationPassword);
                    if (is_array($result)) {
                        return $this->customResult(false, $result);
                    }
                    $resultStaff->setPassword($resultStaff->getEncodedPassword($newPassword));
                    try {
                        $this->staffRepository->save($resultStaff);
                        return $this->customResult(true, ['Success! Your Password has been changed!']);
                    } catch (Exception $e) {
                        throw new CouldNotSaveException(__($e->getMessage()));
                    }
                }
            } else {
                return $this->customResult(false, ['Username not exist in system']);
            }
        }

        return $this->customResult(false, ['Username not exist in system']);
    }
}
