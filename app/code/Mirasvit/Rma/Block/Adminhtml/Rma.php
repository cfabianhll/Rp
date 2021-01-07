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



namespace Mirasvit\Rma\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Rma extends Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller     = 'adminhtml_rma';
        $this->_blockGroup     = 'Mirasvit_Rma';
        $this->_headerText     = __('RMA');
        $this->_addButtonLabel = __('Add New RMA');

        parent::_construct();

        $url = $this->getUrl(
            '*/*/add',
            [
                'order_id' => 'offline',
            ]
        );
        $this->addButton('offline_order', [
            'label'   => __('Create Offline'),
            'onclick' => 'window.location = \'' . $url . '\';',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/add');
    }
}
