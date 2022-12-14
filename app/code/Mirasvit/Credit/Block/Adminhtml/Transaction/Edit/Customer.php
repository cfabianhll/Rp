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



namespace Mirasvit\Credit\Block\Adminhtml\Transaction\Edit;

class Customer extends \Magento\Backend\Block\Widget
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('transaction/edit/customer.phtml');
    }

    /**
     * @return null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGridBlock()
    {
        if (!$this->hasGridBlock()) {
            $this->setData(
                'grid_block',
                $this->getLayout()->createBlock('\Mirasvit\Credit\Block\Adminhtml\Transaction\Edit\Customer\Grid')
            );
        }

        return $this->getData('grid_block');
    }
}
