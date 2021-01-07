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


namespace Mirasvit\Rma\Model\ResourceModel\Address;

/**
 * @method \Mirasvit\Rma\Model\Address getFirstItem()
 * @method \Mirasvit\Rma\Model\Address getLastItem()
 * @method \Mirasvit\Rma\Model\ResourceModel\Address\Collection|\Mirasvit\Rma\Model\Address[] addFieldToFilter
 * @method \Mirasvit\Rma\Model\ResourceModel\Address\Collection|\Mirasvit\Rma\Model\Address[] setOrder
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Mirasvit\Rma\Model\Address', 'Mirasvit\Rma\Model\ResourceModel\Address');
    }

    /**
     * @param bool $emptyOption
     * @param string $defaultAddress
     * @return array
     */
    public function toOptionArray($emptyOption = false, $defaultAddress = '')
    {
        $arr = [];
        if ($emptyOption) {
            $defaultLabel = __('-- Default Address --');
            if (empty($defaultAddress)) {
                $defaultLabel = __('-- Please Select --');
                $defaultAddress = 0;
            }
            $arr[0] = ['value' => $defaultAddress, 'label' => $defaultLabel];
        }
        /** @var \Mirasvit\Rma\Model\Address $item */
        foreach ($this->addActiveFilter() as $item) {
            $arr[] = ['value' => $item->getAddress(), 'label' => $item->getName()];
        }

        return $arr;
    }

    /**
     * @return $this
     */
    public function addActiveFilter()
    {
        $this->getSelect()
            ->where('is_active', 1)
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        return $this;
    }

     /************************/
}
