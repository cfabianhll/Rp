<?php
namespace Trootech\Townshipcitymapping\Block\Adminhtml\Township;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Trootech\Townshipcitymapping\Model\townshipFactory
     */
    protected $_townshipFactory;

    /**
     * @var \Trootech\Townshipcitymapping\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Trootech\Townshipcitymapping\Model\townshipFactory $townshipFactory
     * @param \Trootech\Townshipcitymapping\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Trootech\Townshipcitymapping\Model\TownshipFactory $TownshipFactory,
        \Trootech\Townshipcitymapping\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_townshipFactory = $TownshipFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('township_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_townshipFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'township_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'township_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'city_id',
					[
						'header' => __('City Id'),
						'index' => 'city_id',
					]
				);
				
				$this->addColumn(
					'township_name',
					[
						'header' => __('Township Name'),
						'index' => 'township_name',
					]
				);
				
				$this->addColumn(
					'city_name',
					[
						'header' => __('City Name'),
						'index' => 'city_name',
					]
				);
				
				$this->addColumn(
					'country_name',
					[
						'header' => __('Country Name'),
						'index' => 'country_name',
					]
				);
				


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'township_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('townshipcitymapping/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('townshipcitymapping/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('township_id');
        //$this->getMassactionBlock()->setTemplate('Trootech_Townshipcitymapping::township/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('township');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('townshipcitymapping/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('townshipcitymapping/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('townshipcitymapping/*/index', ['_current' => true]);
    }

    /**
     * @param \Trootech\Townshipcitymapping\Model\township|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'townshipcitymapping/*/edit',
            ['township_id' => $row->getId()]
        );
		
    }

	

}