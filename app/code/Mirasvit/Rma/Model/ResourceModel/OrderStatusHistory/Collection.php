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



namespace Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory;

/**
 * @method \Mirasvit\Rma\Model\OrderStatusHistory getFirstItem()
 * @method \Mirasvit\Rma\Model\OrderStatusHistory getLastItem()
 * @method \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\Collection addFieldToFilter
 * @method \Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory\Collection setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\OrderStatusHistory', 'Mirasvit\Rma\Model\ResourceModel\OrderStatusHistory');
    }

     /************************/
}
