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

class OfflineOrder extends \Magento\Framework\Model\AbstractModel implements
    \Mirasvit\Rma\Api\Data\OfflineOrderInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_eventPrefix = 'rma_offline_order';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\ResourceModel\OfflineOrder');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::KEY_OFFLINE_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($offlineOrderId)
    {
        return $this->setData(self::KEY_OFFLINE_ORDER_ID, $offlineOrderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsOffline()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreditmemo()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOfflineOrderId()
    {
        return $this->getData(self::KEY_OFFLINE_ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOfflineOrderId($offlineOrderId)
    {
        return $this->setData(self::KEY_OFFLINE_ORDER_ID, $offlineOrderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getReceiptNumber()
    {
        return $this->getData(self::KEY_RECEIPT_NUMBER);
    }

    /**
     * {@inheritdoc}
     */
    public function setReceiptNumber($receiptNumber)
    {
        return $this->setData(self::KEY_RECEIPT_NUMBER, $receiptNumber);
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
    public function getStoreId()
    {
        return $this->getData(self::KEY_STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::KEY_STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key = '', $index = null)
    {
        $this->_data['is_offline'] = true;

        return parent::getData($key, $index);
    }
}
