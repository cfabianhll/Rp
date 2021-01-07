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

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Extended as GridExtended;
use Magento\Backend\Helper\Data as BackendHelper;
use Mirasvit\Rma\Helper\Controller\Rma\Grid as GridHelper;
use Mirasvit\Rma\Helper\StringHelper as StringHelper;
use Mirasvit\Rma\Model\RmaFactory;
use Mirasvit\Rma\Model\StatusFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grid extends GridExtended
{
    /**
     * @var array
     */
    protected $customFilters = [];

    /**
     * @var string
     */
    protected $activeTab;
    /**
     * @var StringHelper
     */
    private $rmaString;
    /**
     * @var \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface
     */
    private $rmaSearchManagement;
    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var GridHelper
     */
    private $gridHelper;
    /**
     * @var RmaFactory
     */
    private $rmaFactory;
    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * Grid constructor.
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param StatusFactory $statusFactory
     * @param RmaFactory $rmaFactory
     * @param GridHelper $gridHelper
     * @param StringHelper $rmaString
     * @param \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        StatusFactory $statusFactory,
        RmaFactory $rmaFactory,
        GridHelper $gridHelper,
        StringHelper $rmaString,
        \Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface $rmaSearchManagement,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->statusFactory       = $statusFactory;
        $this->rmaFactory          = $rmaFactory;
        $this->gridHelper          = $gridHelper;
        $this->rmaString           = $rmaString;
        $this->rmaSearchManagement = $rmaSearchManagement;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rma_grid');
        $this->setDefaultSort('updated_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Add custom filter
     *
     * @param string $field
     * @param string $filter
     * @return $this
     */
    public function addCustomFilter($field, $filter)
    {
        $this->customFilters[$field] = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->rmaFactory->create()
            ->getCollection();
        foreach ($this->customFilters as $key => $value) {
            $collection->addFieldToFilter($key, $value);
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->gridHelper->getIncrementId($this);
        $this->gridHelper->getOrderIncrementId($this);
        $this->gridHelper->getUserId($this);
        $this->gridHelper->getLastReplyName($this);
        $this->gridHelper->getStatusId($this);
        $this->gridHelper->getCreatedAt($this);
        $this->gridHelper->getUpdatedAt($this);
        $this->gridHelper->getStoreId($this);
        $this->gridHelper->getItems($this);
        $this->gridHelper->getAction($this);
        $this->gridHelper->getCustomFields($this);

        return parent::_prepareColumns();
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid    $renderedValue
     * @param \Mirasvit\Rma\Model\Rma                   $rma
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function itemsFormat($renderedValue, $rma, $column, $isExport)
    {
        $html = [];
        foreach ($this->rmaSearchManagement->getItems($rma) as $item) {
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $s = '<b>' . $orderItem->getName() . '</b>';
            $s .= ' / ';
            $s .= $item->getReasonName() ? $item->getReasonName() : '-';
            $s .= ' /  ';
            $s .= $item->getConditionName() ? $item->getConditionName() : '-';
            $s .= ' / ';
            $s .= $item->getResolutionName() ? $item->getResolutionName() : '-';

            $html[] = $s;
        }

        return implode('<br>', $html);
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid    $renderedValue
     * @param \Mirasvit\Rma\Model\Rma                   $rma
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _lastReplyFormat($renderedValue, $rma, $column, $isExport)
    {
        $name = $rma->getLastReplyName();
        // If last message is automated, assign Last Reply Name value to owner, if such exists
        $lastMessage = $this->rmaSearchManagement->getLastMessage($rma);
        if ($lastMessage && !$lastMessage->getUserId() && !$lastMessage->getCustomerId()) {
            $name = '';
        }

        if (!$rma->getIsAdminRead()) {
            $name .= ' <img src="' . $this->_assetRepo->getUrl('Mirasvit_Rma::images/fam_newspaper.gif') . '">';
        }

        return $name;
    }

    /**
     * @param \Mirasvit\Rma\Block\Adminhtml\Rma\Grid    $renderedValue
     * @param \Mirasvit\Rma\Model\Rma                   $rma
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @param bool                                      $isExport
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _lastActivityFormat($renderedValue, $rma, $column, $isExport)
    {
        return $this->rmaString->nicetime(strtotime($rma->getUpdatedAt()));
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareMassaction()
    {
//        $this->setMassactionIdField('rma_id');
//        $this->getMassactionBlock()->setFormFieldName('rma_id');
//        $this->getMassactionBlock()->addItem('delete', [
//            'label'   => __('Delete'),
//            'url'     => $this->getUrl('*/*/massDelete'),
//            'confirm' => __('Are you sure?'),
//        ]);
//
//        $statuses = $this->statusFactory->create()->getCollection()->getOptionArray();
//        $this->getMassactionBlock()->addItem('status_id', [
//            'label'      => __('Change Status'),
//            'url'        => $this->getUrl('*/*/massStatus', ['_current' => true]),
//            'additional' => [
//                'visibility' => [
//                    'name'   => 'status_id',
//                    'type'   => 'select',
//                    'class'  => 'required-entry',
//                    'label'  => __('Status'),
//                    'values' => $statuses,
//                ],
//            ],
//        ]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('rma/rma/edit', ['id' => $row->getId()]);
    }

    /**
     * Set active tab
     *
     * @param string $tabName
     * @return void
     */
    public function setActiveTab($tabName)
    {
        $this->activeTab = $tabName;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        if ($this->activeTab) {
            return parent::getGridUrl() . '?active_tab=' . $this->activeTab;
        }

        return parent::getGridUrl();
    }
}
