<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Model\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Mageplaza\GiftCard\Helper\Data;

/**
 * Class Pool
 * @package Mageplaza\GiftCard\Model\ResourceModel
 */
class Pool extends AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('mageplaza_giftcard_pool', 'pool_id');
    }

    /**
     * @param AbstractModel|\Mageplaza\GiftCard\Model\GiftCard $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);

        $templateFields = $object->getTemplateFields();

        if (is_array($templateFields)) {
            $object->setTemplateFields(Data::jsonEncode($templateFields));
        }

        if (is_object($templateFields)) {
            $object->setTemplateFields(Data::jsonEncode($templateFields->getData()));
        }

        return $this;
    }

    /**
     * Perform actions after object load
     *
     * @param AbstractModel|DataObject $object
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);

        if ($object->getTemplateFields()) {
            $object->addData(Data::jsonDecode($object->getTemplateFields()));
        }

        return $this;
    }
}
