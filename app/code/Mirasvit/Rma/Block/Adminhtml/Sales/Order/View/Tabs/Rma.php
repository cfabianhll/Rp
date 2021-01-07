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



namespace Mirasvit\Rma\Block\Adminhtml\Sales\Order\View\Tabs;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Mirasvit\Rma\Api\Service\Order\OrderManagementInterface as RmaHelper;

class Rma extends Widget implements TabInterface
{
    /**
     * @var RmaHelper
     */
    private $rmaHelper;

    /**
     * Rma constructor.
     * @param RmaHelper $rmaHelper
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RmaHelper $rmaHelper,
        Context $context,
        array $data = []
    ) {
        $this->rmaHelper = $rmaHelper;

        parent::__construct($context, $data);
    }

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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $id = $this->getRequest()->getParam('order_id');
        $rmaNewUrl = $this->getUrl('rma/rma/add', ['order_id' => $id]);
        $button = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
            ->setClass('add')
            ->setType('button')
            ->setOnClick('window.location.href=\'' . $rmaNewUrl . '\'')
            ->setLabel(__('Create RMA for this order'));

        /** @var \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid */
        $grid = $this->getLayout()->createBlock('\Mirasvit\Rma\Block\Adminhtml\Rma\Grid');
        $grid->setId('rma_grid_internal');
        $grid->setActiveTab('RMA');
        $grid->addCustomFilter('order_id', $id);
        $grid->setFilterVisibility(false);
        $grid->setExportVisibility(false);
        $grid->setPagerVisibility(false);

        $grid->setTabMode(true);

        if ($this->rmaHelper->isReturnAllowed($id)) {
            $meetMessage = __('Order meets RMA policy');
        } else {
            $meetMessage = __('Order doesn\'t meet RMA policy');
        }

        return '<br>
        <div>' . $button->toHtml() . '<div style="float:right;color:#eb5e00"><i>' . $meetMessage . '</i></div>
        <br><br>' . $grid->toHtml() . '</div>';
    }
}
