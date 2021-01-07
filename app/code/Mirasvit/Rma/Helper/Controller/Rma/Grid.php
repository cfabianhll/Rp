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



namespace Mirasvit\Rma\Helper\Controller\Rma;

use Magento\Framework\Escaper;

class Grid
{
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var \Mirasvit\Rma\Helper\User\Html
     */
    private $rmaUserHtml;
    /**
     * @var \Mirasvit\Rma\Model\StatusFactory
     */
    private $statusFactory;
    /**
     * @var \Mirasvit\Rma\Helper\Store
     */
    private $rmaStoreHelper;
    /**
     * @var \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface
     */
    private $fieldManagement;

    /**
     * Grid constructor.
     * @param \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement
     * @param \Mirasvit\Rma\Helper\Store $rmaStoreHelper
     * @param \Mirasvit\Rma\Model\StatusFactory $statusFactory
     * @param \Mirasvit\Rma\Helper\User\Html $rmaUserHtml
     * @param Escaper $escaper
     */
    public function __construct(
        \Mirasvit\Rma\Api\Service\Field\FieldManagementInterface $fieldManagement,
        \Mirasvit\Rma\Helper\Store $rmaStoreHelper,
        \Mirasvit\Rma\Model\StatusFactory $statusFactory,
        \Mirasvit\Rma\Helper\User\Html $rmaUserHtml,
        Escaper $escaper
    ) {
        $this->fieldManagement = $fieldManagement;
        $this->rmaStoreHelper  = $rmaStoreHelper;
        $this->statusFactory   = $statusFactory;
        $this->rmaUserHtml     = $rmaUserHtml;
        $this->escaper         = $escaper;
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getIncrementId($grid)
    {
            $grid->addColumn('increment_id', [
                'header'       => __('RMA #'),
                'index'        => 'increment_id',
                'filter_index' => 'main_table.increment_id',
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getOrderIncrementId($grid)
    {
            $grid->addColumn('order_increment_id', [
                'header'       => __('Order #'),
                'index'        => 'order_increment_id',
                'filter_index' => 'order.increment_id',
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getUserId($grid)
    {
            $grid->addColumn('user_id', [
                'header'       => __('Owner'),
                'index'        => 'user_id',
                'filter_index' => 'main_table.user_id',
                'type'         => 'options',
                'options'      => $this->rmaUserHtml->getAdminUserOptionArray(),
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getLastReplyName($grid)
    {
            $grid->addColumn('last_reply_name', [
                'header'         => __('Last Replier'),
                'index'          => 'last_reply_name',
                'filter_index'   => 'main_table.last_reply_name',
                'frame_callback' => [$grid, '_lastReplyFormat'],
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getStatusId($grid)
    {
            $grid->addColumn('status_id', [
                'header'       => __('Status'),
                'index'        => 'status_id',
                'filter_index' => 'main_table.status_id',
                'type'         => 'options',
                'options'      => $this->statusFactory->create()->getCollection()->getOptionArray(),
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getCreatedAt($grid)
    {
            $grid->addColumn('created_at', [
                'header'       => __('Created Date'),
                'index'        => 'created_at',
                'filter_index' => 'main_table.created_at',
                'type'         => 'datetime',
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getUpdatedAt($grid)
    {
            $grid->addColumn('updated_at', [
                'header'         => __('Last Activity'),
                'index'          => 'updated_at',
                'filter_index'   => 'main_table.updated_at',
                'type'           => 'datetime',
                'frame_callback' => [$grid, '_lastActivityFormat'],
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getStoreId($grid)
    {
            $grid->addColumn('store_id', [
                'header'       => __('Store'),
                'index'        => 'store_id',
                'filter_index' => 'main_table.store_id',
                'type'         => 'options',
                'options'      => $this->rmaStoreHelper->getCoreStoreOptionArray(),
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getItems($grid)
    {
            $grid->addColumn('items', [
                'header'           => __('Items'),
                'column_css_class' => 'nowrap',
                'type'             => 'text',
                'frame_callback'   => [$grid, 'itemsFormat'],
            ]);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getAction($grid)
    {
            $grid->addColumn(
                'action',
                [
                    'header'   => __('Action'),
                    'width'    => '50px',
                    'type'     => 'action',
                    'getter'   => 'getId',
                    'actions'  => [
                        [
                            'caption' => __('View'),
                            'url'     => [
                                'base' => 'rma/rma/edit',
                            ],
                            'field'   => 'id',
                        ],
                    ],
                    'filter'   => false,
                    'sortable' => false,
                ]
            );
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid $grid
     * @return void
     */
    public function getCustomFields($grid)
    {
        $collection = $this->fieldManagement->getStaffCollection();
        foreach ($collection as $field) {
                $grid->addColumn($field->getCode(), [
                    'header'  => __($this->escaper->escapeHtml($field->getName())),
                    'index'   => $field->getCode(),
                    'type'    => $field->getGridType(),
                    'options' => $field->getGridOptions(),
                ]);
        }
    }
}