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

class Attachment extends \Magento\Framework\Model\AbstractModel
    implements IdentityInterface, \Mirasvit\Rma\Api\Data\AttachmentInterface
{
    const CACHE_TAG = 'rma_attachment';

    /**
     * {@inheritdoc}
     */
    protected $_cacheTag = 'rma_attachment';

    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_attachment';

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
        $this->_init('Mirasvit\Rma\Model\ResourceModel\Attachment');
    }

    /**
     * {@inheritdoc}
     */
    public function getItemType()
    {
        return $this->getData(self::KEY_ITEM_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemType($itemType)
    {
        return $this->setData(self::KEY_ITEM_TYPE, $itemType);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::KEY_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::KEY_ITEM_ID, $itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getUid()
    {
        return $this->getData(self::KEY_UID);
    }

    /**
     * {@inheritdoc}
     */
    public function setUid($uid)
    {
        return $this->setData(self::KEY_UID, $uid);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::KEY_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::KEY_TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->getData(self::KEY_SIZE);
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size)
    {
        return $this->setData(self::KEY_SIZE, $size);
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->getData(self::KEY_BODY);
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body)
    {
        return $this->setData(self::KEY_BODY, $body);
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
}
