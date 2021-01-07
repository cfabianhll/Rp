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



namespace Mirasvit\Rma\Block\Adminhtml\Customer\Edit\Tabs;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Rma extends Widget implements TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('RMA');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('RMA');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab to html
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getId()) {
            return '';
        }
        $id = $this->getId();
        $rmaNewUrl = $this->getUrl('rma/rma/add', ['customer_id' => $id]);
        $button = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\'' . $rmaNewUrl . '\'')
            ->setLabel(__('Create RMA'));

        /** @var \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid */
        $grid = $this->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Grid');
        $grid->addCustomFilter('main_table.customer_id', $id);
        $grid->setFilterVisibility(false);
        $grid->setExportVisibility(false);
        $grid->setPagerVisibility(false);
        $grid->setTabMode(true);

        return '<div>' . $button->toHtml() . '<br><br>' . $grid->toHtml() . '</div>';
    }
}
