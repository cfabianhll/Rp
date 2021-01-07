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



namespace Mirasvit\Rma\Block\Adminhtml\Rma;

class Create extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_rma';
        $this->_mode = 'create';
        $this->_blockGroup = 'Mirasvit_Rma';

        parent::_construct();

        $this->setId('rma_rma_create');
        $this->removeButton('save');
        $this->removeButton('reset');

        if (!$this->isOrdersIdsIsset()){
            $this->buttonList->add(
                'save_and_continue_edit',
                [
                    'class'   => 'primary',
                    'label'   => __('Continue'),
                    'onclick' => "
                        jQuery('#rma_rma_create_order_grid_massaction-select').val('selected_orders');
                        rma_rma_create_order_grid_massactionJsObject.apply();
                    ",
                ],
                10
            );
        }
    }  

    /**
     * {@inheritdoc}
     */
    public function getHeaderText()
    {
        return __('Create New RMA');
    }

    public function isOrdersIdsIsset()
    {
        return $this->getRequest()->getParam('orders_id') ? true : false;
    }
}
