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

/**
 * @method \Mirasvit\Rma\Model\ResourceModel\Rule\Collection|\Mirasvit\Rma\Model\Rule[] getCollection()
 * @method \Mirasvit\Rma\Model\Rule load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rma\Model\Rule setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rma\Model\Rule setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rma\Model\ResourceModel\Rule getResource()
 *
 */
class Rule extends \Magento\Rule\Model\AbstractModel
    implements \Mirasvit\Rma\Api\Data\RuleInterface, IdentityInterface
{
    /**
     * @var Rule\Action\CollectionFactory
     */
    private $ruleActionCollectionFactory;
    /**
     * @var Rule\Condition\CombineFactory
     */
    private $ruleConditionCombineFactory;
    /**
     * @var \Mirasvit\Rma\Helper\Locale
     */
    private $localeData;

    /**
     * Rule constructor.
     * @param Rule\Condition\CombineFactory $ruleConditionCombineFactory
     * @param Rule\Action\CollectionFactory $ruleActionCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Mirasvit\Rma\Helper\Locale $localeData
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Mirasvit\Rma\Model\Rule\Condition\CombineFactory $ruleConditionCombineFactory,
        \Mirasvit\Rma\Model\Rule\Action\CollectionFactory $ruleActionCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Mirasvit\Rma\Helper\Locale $localeData,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ruleConditionCombineFactory = $ruleConditionCombineFactory;
        $this->ruleActionCollectionFactory = $ruleActionCollectionFactory;
        $this->localeData = $localeData;

        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->localeData->getLocaleValue($this, self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->localeData->setLocaleValue($this, self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent()
    {
        return $this->getData(self::KEY_EVENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setEvent($event)
    {
        return $this->setData(self::KEY_EVENT, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailBody()
    {
        return $this->localeData->getLocaleValue($this, self::KEY_EMAIL_BODY);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailBody($emailBody)
    {
        return $this->localeData->setLocaleValue($this, self::KEY_EMAIL_BODY, $emailBody);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailSubject()
    {
        return $this->localeData->getLocaleValue($this, self::KEY_EMAIL_SUBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSubject($emailSubject)
    {
        return $this->localeData->setLocaleValue($this, self::KEY_EMAIL_SUBJECT, $emailSubject);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->getData(self::KEY_IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getConditions()
    {
        $condition = null;
        try {
            $condition = parent::getConditions();
        } catch (\Exception $e) {
            if ($serializeObj = $this->getSerializer()) {
                $origin = clone $this->serializer;
                $this->serializer = $serializeObj;
                $condition = parent::getConditions();
                $this->serializer = $origin;
            }
        }

        return $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::KEY_CONDITIONS_SERIALIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::KEY_CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * @return bool|\Magento\Framework\Serialize\Serializer\Json
     */
    protected function getSerializer()
    {
        $serializer = false;
        if (class_exists(\Magento\Framework\Serialize\Serializer\Serialize::class)) {
            $serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\Serializer\Serialize::class
            );
        }

        return $serializer;

    }

    /**
     * {@inheritdoc}
     */
    public function getIsSendOwner()
    {
        return $this->getData(self::KEY_IS_SEND_OWNER);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSendOwner($isSendOwner)
    {
        return $this->setData(self::KEY_IS_SEND_OWNER, $isSendOwner);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSendDepartment()
    {
        return $this->getData(self::KEY_IS_SEND_DEPARTMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSendDepartment($isSendDepartment)
    {
        return $this->setData(self::KEY_IS_SEND_DEPARTMENT, $isSendDepartment);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsSendUser()
    {
        return $this->getData(self::KEY_IS_SEND_USER);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSendUser($isSendUser)
    {
        return $this->setData(self::KEY_IS_SEND_USER, $isSendUser);
    }

    /**
     * @return string
     */
    public function getOtherEmail()
    {
        return $this->getData(self::KEY_OTHER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setOtherEmail($otherEmail)
    {
        return $this->setData(self::KEY_OTHER_EMAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function getSortOrder()
    {
        return $this->getData(self::KEY_SORT_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsStopProcessing()
    {
        return $this->getData(self::KEY_IS_STOP_PROCESSING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsStopProcessing($isStopProcessing)
    {
        return $this->setData(self::KEY_IS_STOP_PROCESSING, $isStopProcessing);
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
    public function getIsSendAttachment()
    {
        return $this->getData(self::KEY_IS_SEND_ATTACHMENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSendAttachment($isSendAttachment)
    {
        return $this->setData(self::KEY_IS_SEND_ATTACHMENT, $isSendAttachment);
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
     * @return \Mirasvit\Rma\Api\Data\RuleInterface|Rule
     */
    public function setIsResolved($isResolved)
    {
        return $this->setData(self::KEY_IS_RESOLVED, $isResolved);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) @fixme
     */
    public function toString($format = '')
    {
        $this->load($this->getId());
        $string = $this->getConditions()->asStringRecursive();

        $string = nl2br(preg_replace('/ /', '&nbsp;', $string));

        return $string;
    }


    const CACHE_TAG = 'rma_rule';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_rule';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_rule';

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Rule');
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsInstance()
    {
        return $this->ruleConditionCombineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsInstance()
    {
        return $this->ruleActionCollectionFactory->create();
    }
}
