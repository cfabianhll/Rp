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
 * @package   mirasvit/module-credit
 * @version   1.0.79
 * @copyright Copyright (C) 2020 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Credit\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Credit\Api\Data\TransactionInterface;

class Transaction extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(TransactionInterface::TABLE_NAME, TransactionInterface::ID);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getId()) {
            $object->setData(
                TransactionInterface::CREATED_AT,
                (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT)
            );
        }

        $object->setData(
            TransactionInterface::UPDATED_AT,
            (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT)
        );

        return parent::_beforeSave($object);
    }
}
