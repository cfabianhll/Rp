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
use Mirasvit\Rma\Api\Data\MessageInterface;

/**
 * @method ResourceModel\Message\Collection|Message[] getCollection()
 * @method Message load(int $id)
 * @method bool getIsMassDelete()
 * @method Message setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method Message setIsMassStatus(bool $flag)
 * @method ResourceModel\Message getResource()
 */
class Message extends \Magento\Framework\Model\AbstractModel
    implements IdentityInterface, \Mirasvit\Rma\Api\Data\MessageInterface
{
    const CACHE_TAG = 'rma_message';
    const KEY_RMA_ID = 'rma_id';
    const KEY_USER_ID = 'user_id';
    const KEY_CUSTOMER_ID = 'customer_id';
    const KEY_CUSTOMER_NAME = 'customer_name';
    const KEY_TEXT = 'text';
    const KEY_IS_HTML = 'is_html';
    const KEY_IS_VISIBLE_IN_FRONTEND = 'is_visible_in_frontend';
    const KEY_IS_CUSTOMER_NOTIFIED = 'is_customer_notified';
    const KEY_CREATED_AT = 'created_at';
    const KEY_UPDATED_AT = 'updated_at';
    const KEY_EMAIL_ID = 'email_id';
    const KEY_IS_READ = 'is_read';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_message';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_message';

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
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Message');
    }

    /**
     * {@inheritdoc}
     */
    public function getRmaId()
    {
        return $this->getData(self::KEY_RMA_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRmaId($rmaId)
    {
        return $this->setData(self::KEY_RMA_ID, $rmaId);
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
    public function getCustomerName()
    {
        return $this->getData(self::KEY_CUSTOMER_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerName($customerName)
    {
        return $this->setData(self::KEY_CUSTOMER_NAME, $customerName);
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return $this->getData(self::KEY_TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function setText($text)
    {
        return $this->setData(self::KEY_TEXT, $text);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsHtml()
    {
        return $this->getData(self::KEY_IS_HTML);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsHtml($isHtml)
    {
        return $this->setData(self::KEY_IS_HTML, $isHtml);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVisibleInFrontend()
    {
        return $this->getData(self::KEY_IS_VISIBLE_IN_FRONTEND);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVisibleInFrontend($isVisibleInFrontend)
    {
        return $this->setData(self::KEY_IS_VISIBLE_IN_FRONTEND, $isVisibleInFrontend);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(self::KEY_IS_CUSTOMER_NOTIFIED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsCustomerNotified($isCustomerNotified)
    {
        return $this->setData(self::KEY_IS_CUSTOMER_NOTIFIED, $isCustomerNotified);
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
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
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
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailId()
    {
        return $this->getData(self::KEY_EMAIL_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailId($emailId)
    {
        return $this->setData(self::KEY_EMAIL_ID, $emailId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsRead()
    {
        return $this->getData(self::KEY_IS_READ);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsRead($isRead)
    {
        return $this->setData(self::KEY_IS_READ, $isRead);
    }
}
