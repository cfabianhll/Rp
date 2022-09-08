<?php


namespace Trootech\Townshipcitymapping\Controller\Index;


use Magento\Framework\App\Action\Context;

class Collection extends \Magento\Framework\App\Action\Action
{

    protected $_townshipFactory;
    public function __construct(

        Context $context,
        \Trootech\Townshipcitymapping\Model\ResourceModel\Township\CollectionFactory $TownshipFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_townshipFactory = $TownshipFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $cityId = $this->getRequest()->getParam('city_id');
        $resultJson = $this->resultJsonFactory->create();
        $cityCollection = $this->_townshipFactory->create();

        // $cityCollection->addFieldToSelect('township_id');
        $cityCollection->addFieldToSelect('*')->addFieldToFilter('city_name',  ['eq'=> $cityId]);

        // $cityCollection
        //$cityCollection->printlogQuery(True);
        //$array = array();
        $html = '<option value="">'.__('Corregimiento').'</option>';



        if (sizeof($cityCollection)):

            foreach ($cityCollection as $data)
            {
               $html .= '<option value="'.$data->getData('township_id').'">'.$data->getData('township_name').'</option>';
            }
            endif;


        return $resultJson->setData([
            'data' =>  $html
        ]);


    }

}
