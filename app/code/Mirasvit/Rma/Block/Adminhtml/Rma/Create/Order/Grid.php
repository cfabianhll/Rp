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



namespace Mirasvit\Rma\Block\Adminhtml\Rma\Create\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\Address\Renderer;
use Mirasvit\Rma\Api\Config\RmaPolicyConfigInterface;
use Mirasvit\Rma\Api\Service\Order\OrderManagementInterface;
use Mirasvit\Rma\Helper\Mage;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var OrderManagementInterface
     */
    private $orderManagementService;
    /**
     * @var Renderer
     */
    private $addressRenderer;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var Mage
     */
    private $rmaMage;
    /**
     * @var RmaPolicyConfigInterface
     */
    private $policyConfig;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Grid constructor.
     * @param Renderer $addressRenderer
     * @param Context $context
     * @param Data $backendHelper
     * @param OrderRepository $orderRepository
     * @param OrderManagementInterface $orderManagementService
     * @param Mage $rmaMage
     * @param RmaPolicyConfigInterface $policyConfig
     * @param array $data
     */
    public function __construct(
        Renderer $addressRenderer,
        Context $context,
        Data $backendHelper,
        OrderRepository $orderRepository,
        OrderManagementInterface $orderManagementService,
        Mage $rmaMage,
        RmaPolicyConfigInterface $policyConfig,
        array $data = []
    ) {
        $this->addressRenderer = $addressRenderer;
        $this->context         = $context;
        $this->request         = $context->getRequest();
        $this->rmaMage         = $rmaMage;
        $this->policyConfig    = $policyConfig;
        $this->orderRepository = $orderRepository;
        $this->orderManagementService = $orderManagementService;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('rma_rma_create_order_grid');
        $this->setDefaultSort('increment_id');
        $this->setSaveParametersInSession(true);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $allowedStatuses = $this->policyConfig->getAllowRmaInOrderStatuses();
        $collection = $this->rmaMage->getOrderCollection();
        $collection->addFieldToFilter('status', ['in' => $allowedStatuses]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', [
            'header' => __('Order #'),
            'width' => '80px',
            'type' => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ]);

        if (!$this->context->getStoreManager()->isSingleStoreMode()) {
            $this->addColumn('store_id', [
                'header' => __('Purchased From (Store)'),
                'index' => 'store_id',
                'type' => 'store',
                'store_view' => true,
                'display_deleted' => true,
            ]);
        }

        $this->addColumn('created_at', [
            'header' => __('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ]);

        $this->addColumn('billing_name', [
            'header' => __('Bill to Name'),
            'index' => 'billing_name',
        ]);

        $this->addColumn('shipping_name', [
            'header' => __('Ship to Name'),
            'index' => 'shipping_name',
        ]);

        $this->addColumn('base_grand_total', [
            'header' => __('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ]);

        $this->addColumn('grand_total', [
            'header' => __('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code',
        ]);

        $this->addColumn('entity_id', [
            'header' => __('RMA Allows'),
            'index' => 'entity_id',
            'sortable' => false,
            'renderer' => '\Mirasvit\Rma\Block\Adminhtml\Rma\Create\Order\Column\AllowColumn'
        ]);

        $this->addColumn('other_rmas', [
            'header' => __('RMAs'),
            'index' => 'entity_id',
            'sortable' => false,
            'renderer' => '\Mirasvit\Rma\Block\Adminhtml\Rma\Create\Order\Column\OtherRmasColumn'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Prepares mass actions for a grid.
     *
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('selected_orders');
        $this->getMassactionBlock()->setUseSelectAll(true);

        $data = [];
        if ($this->request->getParam('ticket_id', 0)) {
            $data = [
                'ticket_id' => $this->request->getParam('ticket_id'),
            ];
        }

        $this->getMassactionBlock()->addItem('selected_orders', array(
            'label' => __('Create'),
            'url' => $this->getUrl('*/*/massSelectOrders', $data),
        ));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return '';
    }
}
