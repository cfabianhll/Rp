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



namespace Mirasvit\Rma\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Mirasvit\Rma\Api\Data\RmaInterface;

/**
 * @method \Mirasvit\Rma\Model\ResourceModel\Rma\Collection|\Mirasvit\Rma\Model\Rma[] getCollection()
 * @method \Mirasvit\Rma\Model\Rma load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rma\Model\Rma setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rma\Model\Rma setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rma\Model\ResourceModel\Rma getResource()
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Rma extends \Magento\Framework\Model\AbstractModel implements RmaInterface, IdentityInterface
{

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Mirasvit\Rma\Helper\Rma\Data
     */
    private $rmaData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Rma constructor.
     * @param \Mirasvit\Rma\Helper\Rma\Data $rmaData
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Helper\Rma\Data $rmaData,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->orderFactory = $orderFactory;
        $this->rmaData = $rmaData;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncrementId()
    {
        return $this->getData(self::KEY_INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::KEY_INCREMENT_ID, $incrementId);
    }

    /**
     * {@inheritdoc}
     */
    public function getGuestId()
    {
        return $this->getData(self::KEY_GUEST_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGuestId($guestId)
    {
        return $this->setData(self::KEY_GUEST_ID, $guestId);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstname()
    {
        return $this->getData(self::KEY_FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::KEY_FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastname()
    {
        return $this->getData(self::KEY_LASTNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::KEY_LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     */
    public function getCompany()
    {
        return $this->getData(self::KEY_COMPANY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCompany($company)
    {
        return $this->setData(self::KEY_COMPANY, $company);
    }

    /**
     * {@inheritdoc}
     */
    public function getTelephone()
    {
        return $this->getData(self::KEY_TELEPHONE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTelephone($telepone)
    {
        return $this->setData(self::KEY_TELEPHONE, $telepone);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData(self::KEY_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData(self::KEY_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getStreet()
    {
        return $this->getData(self::KEY_STREET);
    }

    /**
     * {@inheritdoc}
     */
    public function setStreet($street)
    {
        return $this->setData(self::KEY_STREET, $street);
    }

    /**
     * {@inheritdoc}
     */
    public function getCity()
    {
        return $this->getData(self::KEY_CITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setCity($city)
    {
        return $this->setData(self::KEY_CITY, $city);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegion()
    {
        return $this->getData(self::KEY_REGION);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegion($region)
    {
        return $this->setData(self::KEY_REGION, $region);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionId()
    {
        return $this->getData(self::KEY_REGION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::KEY_REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryId()
    {
        return $this->getData(self::KEY_COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPostcode()
    {
        return $this->getData(self::KEY_POSTCODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::KEY_POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusId()
    {
        return $this->getData(self::KEY_STATUS_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatusId($statusId)
    {
        return $this->setData(self::KEY_STATUS_ID, $statusId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        if ($this->getData(self::KEY_STORE_ID)) {
            return $this->getData(self::KEY_STORE_ID);
        }

        if ($this->getOrderId()) {
            $order = $this->orderFactory->create()->loadByAttribute('entity_id', $this->getOrderId());
            return $order->getStoreId();
        }

        return $this->storeManager->getDefaultStoreView()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }

    /**
     * @return bool
     */
    public function getIsResolved()
    {
        return $this->getData(self::KEY_IS_RESOLVED);
    }

    /**
     * @param bool $isResolved
     * @return RmaInterface|Rma
     */
    public function setIsResolved($isResolved)
    {
        return $this->setData(self::KEY_IS_RESOLVED, $isResolved);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::KEY_CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::KEY_CREATED_AT, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($date)
    {
        return $this->setData(self::KEY_UPDATED_AT, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsGift()
    {
        return $this->getData(self::KEY_IS_GIFT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsGift($isGift)
    {
        return $this->setData(self::KEY_IS_GIFT, $isGift);
    }

    /**
     * @deprecated need to delete
     * {@inheritdoc}
     */
    public function getIsAdminRead()
    {
        return $this->getData(self::KEY_IS_ADMIN_READ);
    }

    /**
     * @deprecated need to delete
     * {@inheritdoc}
     */
    public function setIsAdminRead($isAdminRead)
    {
        return $this->setData(self::KEY_IS_ADMIN_READ, $isAdminRead);
    }


    /**
     * {@inheritdoc}
     */
    public function getUserId()
    {
        return $this->getData(self::KEY_USER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setUserId($userId)
    {
        return $this->setData(self::KEY_USER_ID, $userId);
    }


    /**
     * {@inheritdoc}
     */
    public function getLastReplyName()
    {
        return $this->getData(self::KEY_LAST_REPLY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastReplyName($lastReplyName)
    {
        return $this->setData(self::KEY_LAST_REPLY_NAME, $lastReplyName);
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketId()
    {
        return $this->getData(self::KEY_TICKET_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTicketId($ticketId)
    {
        return $this->setData(self::KEY_TICKET_ID, $ticketId);
    }


    /**
     * {@inheritdoc}
     */
    public function getExchangeOrderIds()
    {
        return $this->getData(self::KEY_EXCHANGE_ORDER_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setExchangeOrderIds($orderIds)
    {
        return $this->setData(self::KEY_EXCHANGE_ORDER_IDS, $orderIds);
    }

    /**
     * @return int[]
     */
    public function getReplacementOrderIds()
    {
        return $this->getData(self::KEY_REPLACEMENT_ORDER_IDS);
    }

    /**
     * @param int[] $orderIds
     * @return $this
     */
    public function setReplacementOrderIds($orderIds)
    {
        return $this->setData(self::KEY_REPLACEMENT_ORDER_IDS, $orderIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemoIds()
    {
        return $this->getData(self::KEY_CREDIT_MEMO_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditMemoIds($creditMemoIds)
    {
        return $this->setData(self::KEY_CREDIT_MEMO_IDS, $creditMemoIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getReturnAddress()
    {
        return $this->getData(self::KEY_RETURN_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function setReturnAddress($address)
    {
        return $this->setData(self::KEY_RETURN_ADDRESS, $address);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::MESSAGE_CODE.$this->getGuestId();
    }

    /**
     * @return string
     */
    public function getStatusHistory()
    {
        return $this->getData(self::KEY_STATUS_HISTORY);
    }

    /**
     * @param string $statuses
     * @return $this
     */
    public function setStatusHistory($statuses)
    {
        return $this->setData(self::KEY_STATUS_HISTORY, $statuses);
    }

    const CACHE_TAG = 'rma_rma';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_rma';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_rma';

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * @param int $resolutionId
     * @return int
     */
    public function getHasItemsWithResolution($resolutionId)
    {
        return $this->rmaData->getRmaSearchManagement()->hasRmaResolution($this, $resolutionId);
    }

    /**
     * @param int $conditionId
     * @return int
     */
    public function getHasItemsWithCondition($conditionId)
    {
        return $this->rmaData->getRmaSearchManagement()->hasRmaCondition($this, $conditionId);
    }

    /**
     * @param int $reasonId
     * @return int
     */
    public function getHasItemsWithReason($reasonId)
    {
        return $this->rmaData->getRmaSearchManagement()->hasRmaResolution($this, $reasonId);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        parent::afterSave();
        if (!$this->getIncrementId()) {
            $this->setIncrementId($this->rmaData->generateIncrementId($this));
            $this->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Rma');
    }
}
