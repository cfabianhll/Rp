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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Mirasvit\Rma\Api\Data\RmaInterface;
use Mirasvit\Rma\Api\Data\StatusInterface;
use Mirasvit\Rma\Api\Repository\RmaRepositoryInterface;

/**
 * @method ResourceModel\Status\Collection getCollection()
 * @method $this load(int $id)
 * @method bool getIsMassDelete()
 * @method $this setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method $this setIsMassStatus(bool $flag)
 * @method ResourceModel\Status getResource()
 */
class Status extends AbstractModel implements StatusInterface
{
    /**
     * @var \Mirasvit\Rma\Helper\Locale
     */
    private $localeData;

    private $rmaRepository;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Status');
    }

    /**
     * Status constructor.
     * @param RmaRepositoryInterface                                       $rmaRepository
     * @param \Mirasvit\Rma\Helper\Locale                                  $localeData
     * @param \Magento\Framework\Model\Context                             $context
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null           $resourceCollection
     * @param array $data
     */
    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        \Mirasvit\Rma\Helper\Locale $localeData,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->localeData    = $localeData;
        $this->rmaRepository = $rmaRepository;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
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
        $this->localeData->setLocaleValue($this, self::KEY_NAME, $name);

        return $this;
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
    public function getIsShowShipping()
    {
        return $this->getData(self::KEY_IS_SHOW_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsShowShipping($isShowShipping)
    {
        return $this->setData(self::KEY_IS_SHOW_SHIPPING, $isShowShipping);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMessage()
    {
        $message = $this->localeData->getLocaleValue($this, self::KEY_CUSTOMER_MESSAGE);
        return $this->decodeMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerMessage($customerMessage)
    {
        $value = "";
        if ($customerMessage) {
            $value = json_encode($customerMessage);
        }
        $this->localeData->setLocaleValue($this, self::KEY_CUSTOMER_MESSAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminMessage()
    {
        $message = $this->localeData->getLocaleValue($this, self::KEY_ADMIN_MESSAGE);
        return $this->decodeMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdminMessage($adminMessage)
    {
        $value = "";
        if ($adminMessage) {
            $value = json_encode($adminMessage);
        }
        $this->localeData->setLocaleValue($this, self::KEY_ADMIN_MESSAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHistoryMessage()
    {
        $message = $this->localeData->getLocaleValue($this, self::KEY_HISTORY_MESSAGE);
        return $this->decodeMessage($message);
    }

    /**
     * {@inheritdoc}
     */
    public function setHistoryMessage($historyMessage)
    {
        $value = "";
        if ($historyMessage) {
            $value = json_encode($historyMessage);
        }
        $this->localeData->setLocaleValue($this, self::KEY_HISTORY_MESSAGE, $value);

        return $this;
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
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenIds()
    {
        if ($this->getData(self::KEY_CHILDREN_IDS)) {
            return (array)explode(',', $this->getData(self::KEY_CHILDREN_IDS));
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setChildrenIds($ids)
    {
        return $this->setData(self::KEY_CHILDREN_IDS, implode(',', (array)$ids));
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVisible()
    {
        return $this->getData(self::KEY_IS_VISIBLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVisible($visible)
    {
        return $this->setData(self::KEY_IS_VISIBLE, $visible);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsMainBranch()
    {
        return $this->getData(self::KEY_IS_MAIN_BRANCH);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsMainBranch($mainBranch)
    {
        return $this->setData(self::KEY_IS_MAIN_BRANCH, $mainBranch);
    }

    /**
     * {@inheritdoc}
     */
    public function getColor()
    {
        return $this->getData(self::KEY_COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setColor($color)
    {
        return $this->setData(self::KEY_COLOR, $color);
    }

    /**
     * {@inheritdoc}
     */
    public function addData(array $data)
    {
        if (!empty($data[self::KEY_CHILDREN_IDS]) && is_array($data[self::KEY_CHILDREN_IDS])) {
            $data[self::KEY_CHILDREN_IDS] = implode(',', $data[self::KEY_CHILDREN_IDS]);
        }

        return parent::addData($data);
    }
    /**
     * Compatibility to old versions
     *
     * @param string $message
     * @return string
     */
    public function decodeMessage($message)
    {
        if ($decoded = json_decode($message, true)) {
            $message = $decoded;
        }
        if (is_array($message)) {
            return $message;
        } else {
            return [$message];
        }
    }

    /**
     * @inheritDoc
     */
    function beforeDelete()
    {
        if ($this->isStatusUsed()) {
            throw new LocalizedException(
                __('Status "%1" is used in the existing RMA(s). Please remove the status from RMA(s)', $this->getName())
            );
        }
        return parent::beforeDelete();
    }

    /**
     * @return bool
     */
    private function isStatusUsed()
    {
        $collection = $this->rmaRepository->getCollection()
            ->addFieldToFilter('main_table.'.RmaInterface::KEY_STATUS_ID, $this->getId())
        ;

        return $collection->count() > 0;
    }
}
