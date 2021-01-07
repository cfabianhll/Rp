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
 * @method \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\Collection getCollection()
 * @method \Mirasvit\Rma\Model\OrderStatusHistory load(int $id)
 * @method bool getIsMassDelete()
 * @method \Mirasvit\Rma\Model\OrderStatusHistory setIsMassDelete(bool $flag)
 * @method bool getIsMassStatus()
 * @method \Mirasvit\Rma\Model\OrderStatusHistory setIsMassStatus(bool $flag)
 * @method \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory getResource()
 */
class OrderStatusHistory extends \Magento\Framework\Model\AbstractModel
    implements \Mirasvit\Rma\Api\Data\OrderStatusHistoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHistoryId()
    {
        return $this->getData(self::KEY_HISTORY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setHistoryId($historyId)
    {
        return $this->setData(self::KEY_HISTORY_ID, $historyId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData(self::KEY_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::KEY_ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::KEY_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::KEY_STATUS, $status);
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
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory');
    }
}
